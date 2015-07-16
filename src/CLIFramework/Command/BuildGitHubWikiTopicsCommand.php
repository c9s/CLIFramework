<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use CodeGen\Block;
use ClassTemplate\ClassFile;
use ClassTemplate\TemplateClassFile;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RecursiveRegexIterator;
use Exception;

class BuildGitHubWikiTopicsCommand extends Command
{
    public function brief() {
        return 'Build topic classes from the wiki of a GitHub Project.';
    }

    public function options($opts) 
    {
        $opts->add('ns:', 'Class namespace');
        $opts->add('dir:', 'Output directory')
            ;
        $opts->add('update', 'Update wiki repository');
    }

    public function arguments($args) 
    {
        $args->add('user')->desc('GitHub Account');
        $args->add('repo')->desc('GitHub Repository');
    }

    public function execute($user, $repo) {
        $ns = '';
        if ($appNs = $this->options->ns) {
            $ns = rtrim(str_replace(':','\\',$appNs),'\\') . '\\';
        } elseif ($appNs = $this->getApplication()->getCurrentAppNamespace()) {
            $ns = $appNs . '\\Topic\\';
        } else {
            $this->logger->notice('Namespace is defined.');
        }

        // Use git to clone the wiki
        $wikiGitURI = "https://github.com/$user/$repo.wiki.git";
        $wikiBaseUrl = "https://github.com/$user/$repo/wiki";
        $localRepoPath = "tmp/$repo.wiki";

        $currentDir = getcwd();

        if (is_dir($localRepoPath)) {
            if ($this->options->update) {
                $this->logger->info("Fetching $wikiGitURI...");
                system("git -C $localRepoPath pull origin", $retval);
                if ($retval != 0) {
                    return $this->logger->error("Can't clone wiki repository");
                }
            }
        } else {
            $dirname = dirname($localRepoPath);
            if (!file_exists($dirname)) {
                mkdir($dirname, 0755, true);
            }

            $this->logger->info("Cloning $wikiGitURI...");
            system("git clone $wikiGitURI $localRepoPath", $retval);
            if ($retval != 0) {
                return $this->logger->error("Can't clone wiki repository");
            }
        }

        // Build classes
        $this->logger->info("Building topic classes from GitHub pages...");

        $outputDir = $this->options->dir ?: '.';

        $directory = new RecursiveDirectoryIterator($localRepoPath);
        $iterator = new RecursiveIteratorIterator($directory);

        $topics = array();
        foreach($iterator as $file) {
            if (preg_match('/\.git/',$file->getPathName())) {
                continue;
            }
            if (preg_match('/\.md$/', $file->getPathName())) {
                $topicRemoteUrl = $wikiBaseUrl . '/' . $file->getFileName();

                // Used from command-line, to invoke the topic
                // $topicId = strtolower(preg_replace(array('/.md$/'),array(''),$file->getFileName()));

                // The readable topic title
                // TODO: Support non-ascii characters 
                $entryName = preg_replace('/.md$/','',$file->getFileName());
                $entryNameNonEnChars = trim(preg_replace('/[^a-zA-Z0-9-\s]/', '', $entryName));
                $topicClassName = join("", array_map("ucfirst", explode('-', str_replace(' ','', $entryNameNonEnChars)))) . 'Topic';
                $topicId = strtolower(preg_replace('/\s+/','-', $entryNameNonEnChars));
                $topicTitle = preg_replace('/-/', ' ', $entryName);
                $topicFullClassName = $ns . $topicClassName;

                $topics[ $topicId ] = $topicFullClassName;

                $classFile = $outputDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $topicFullClassName) . '.php';

                $cTemplate = new TemplateClassFile($topicFullClassName , array(
                    'template' => 'Class.php.twig',
                ));

                $cTemplate->addProperty('id',  $topicId);
                $cTemplate->addProperty('url', $topicRemoteUrl);
                $cTemplate->addProperty('title', $topicTitle);

                $cTemplate->extendClass('\\CLIFramework\\Topic\\GitHubTopic');

                $cTemplate->addMethod('public','getRemoteUrl', array(), 'return $this->remoteUrl;');
                $cTemplate->addMethod('public','getId', array(), 'return $this->id;');

                $content = file_get_contents($file);
                $cTemplate->addMethod('public','getContent', array(), 'return ' . var_export($content, true) . ';', array(), false);

                $classDir = dirname($classFile);
                if (!file_exists($classDir)) {
                    mkdir($classDir,0755, true);
                }
                $this->logger->info("Creating $classFile");
                if ( false === file_put_contents($classFile, $cTemplate->render())) {
                    throw new Exception("Can't write file $classFile.");
                }
            }

        }

        $this->logger->info(wordwrap("You may now copy the below content to the 'init' method in your application class:"));

        $this->logger->writeln('-------');
        $block = new Block();
        $block->appendLine('$this->topics(' . var_export($topics, true) . ');');
        $this->logger->write($block->render());
        $this->logger->writeln('-------');

        chdir($currentDir);
        $this->logger->info("Done");
    }
}




