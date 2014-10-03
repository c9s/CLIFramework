<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use ClassTemplate\ClassTemplate;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RecursiveRegexIterator;


class BuildGitHubWikiTopicsCommand extends Command
{

    public function brief() { return 'Build topic classes from the wiki of a GitHub Project'; }

    public function options($opts) {
        $opts->add('ns:', 'Class namespace');
        $opts->add('dir:', 'Output directory');
    }

    public function arguments($args) {
        $args->add('user')->desc('GitHub Account');
        $args->add('repo')->desc('GitHub Repository');
    }

    public function execute($user, $repo) {
        $ns = $this->options->ns;

        // Use git to clone the wiki
        $wikiGitURI = "https://github.com/$user/$repo.wiki.git";
        $wikiBaseUrl = "https://github.com/$user/$repo/wiki";
        $localRepoPath = "/tmp/$repo.wiki";

        $currentDir = getcwd();

        if (is_dir($localRepoPath)) {
            $this->logger->info("Fetching...");
            system("git -C $localRepoPath pull origin", $retval);
            if ($retval != 0) {
                return $this->logger->error("Can't clone wiki repository");
            }
        } else {
            system("git clone $wikiGitURI $localRepoPath", $retval);
            if ($retval != 0) {
                return $this->logger->error("Can't clone wiki repository");
            }
        }

        // Build classes
        $this->logger->info("Building topic classes from GitHub pages...");

        $directory = new RecursiveDirectoryIterator($localRepoPath);
        $iterator = new RecursiveIteratorIterator($directory);
        foreach($iterator as $file) {
            if ( preg_match('/\.git/',$file->getPathName())) {
                continue;
            }
            if (preg_match('/\.md$/', $file->getPathName())) {
                $topicRemoteUrl = $wikiBaseUrl . '/' . $file->getFileName();

                // Used from command-line, to invoke the topic
                $topicId = strtolower(preg_replace(array('/.md$/'),array(''),$file->getFileName()));

                // The readable topic title
                $topicTitle = preg_replace(array('/.md$/','/-/'),array('',' '),$file->getFileName());

                // The class namename for topic
                $topicClassName = preg_replace(array('/.md$/','/\W/'),array('',''),$file->getFileName()) . "Topic";
                if ($namespace = $this->options->ns) {
                    $topicClassName = str_replace(':','\\',$namespace) . '\\' . $topicClassName;
                }

                $cTemplate = new ClassTemplate($topicClassName , array(
                    'template' => 'Class.php.twig',
                ));

                $cTemplate->addProperty('remoteUrl', $topicRemoteUrl);
                $cTemplate->addProperty('title', $topicTitle);
                $cTemplate->addProperty('id',  $topicId);

                $cTemplate->extendClass('\\CLIFramework\\Topic\\GitHubTopic');

                $cTemplate->addMethod('public','getRemoteUrl', [], 'return $this->remoteUrl;');
                $cTemplate->addMethod('public','getId', [], 'return $this->id;');

                $content = file_get_contents($file);
                $cTemplate->addMethod('public','getContent', [], 'return ' . var_export($content, true) . ';', [] , false);

                $outputDir = $this->options->dir ?: '.';
                $classFile = $outputDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR ,$topicClassName) . '.php';
                $classDir  = dirname($classFile);
                if (!file_exists($classDir)) {
                    mkdir($classDir,0755, true);
                }
                file_put_contents($classFile, $cTemplate->render());
                $this->logger->info("Creating $classFile");
            }
        }

        chdir($currentDir);
        $this->logger->info("Done");
    }
}




