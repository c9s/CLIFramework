<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use ClassTemplate\ClassTemplate;
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
        $opts->add('dir:', 'Output directory');
    }

    public function arguments($args) 
    {
        $args->add('user')->desc('GitHub Account');
        $args->add('repo')->desc('GitHub Repository');
    }

    public function execute($user, $repo) {
        $ns = '';
        if ($appNs = $this->getApplication()->getCurrentAppNamespace()) {
            $ns = $appNs . '\\Topic\\';
        } elseif ($appNs = $this->options->ns) {
            $ns = rtrim(str_replace(':','\\',$appNs),'\\') . '\\';
        } else {
            $this->logger->notice('Namespace is defined.');
        }

        // Use git to clone the wiki
        $wikiGitURI = "https://github.com/$user/$repo.wiki.git";
        $wikiBaseUrl = "https://github.com/$user/$repo/wiki";
        $localRepoPath = "/tmp/$repo.wiki";

        $currentDir = getcwd();

        if (is_dir($localRepoPath)) {
            $this->logger->info("Fetching $wikiGitURI...");
            system("git -C $localRepoPath pull origin", $retval);
            if ($retval != 0) {
                return $this->logger->error("Can't clone wiki repository");
            }
        } else {
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
        foreach($iterator as $file) {
            if (preg_match('/\.git/',$file->getPathName())) {
                continue;
            }
            if (preg_match('/\.md$/', $file->getPathName())) {
                $topicRemoteUrl = $wikiBaseUrl . '/' . $file->getFileName();

                // Used from command-line, to invoke the topic
                $topicId = strtolower(preg_replace(array('/.md$/'),array(''),$file->getFileName()));

                // The readable topic title
                // TODO: Support non-ascii characters 
                $entryName = preg_replace(array('/.md$/','/[^a-zA-Z0-9-]/'),array('',''),$file->getFileName());
                $topicClassName = join("", array_map("ucfirst", explode('-', $entryName))) . 'Topic';
                $topicTitle = preg_replace('/-/', ' ', $entryName);
                $topicFullClassName = $ns . $topicClassName;

                $classFile = $outputDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $topicFullClassName) . '.php';

                $cTemplate = new ClassTemplate($topicFullClassName , array(
                    'template' => 'Class.php.twig',
                ));

                $cTemplate->addProperty('remoteUrl', $topicRemoteUrl);
                $cTemplate->addProperty('title', $topicTitle);
                $cTemplate->addProperty('id',  $topicId);

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

        chdir($currentDir);
        $this->logger->info("Done");
    }
}




