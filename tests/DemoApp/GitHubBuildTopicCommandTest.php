<?php
namespace DemoApp;
use CLIFramework\Testing\CommandTestCase;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;


/**
 * @group github-topic
 */
class GitHubBuildTopicCommandTest extends CommandTestCase
{

    public function setupApplication() {
        return new \DemoApp\Application;
    }

    public function testBuildGitHubTopics()
    {
        $outputDir = 'tests/blah';
        $this->cleanUp("tmp");
        $this->cleanUp($outputDir);

        $this->expectOutputRegex("!Creating .*?/.*?/Topic/ContributionTopic.php!xs");
        $this->runCommand("example/demo github:build-topics --ns PHPBrew:Topic --dir $outputDir phpbrew phpbrew");
        $this->cleanUp($outputDir);
    }

    public function cleanUp($path) {
        if (!file_exists($path)) {
            return;
        }
        $directoryIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($iterator as $file) {
            if (is_file($file)) {
                unlink($file);
            } else if (is_dir($file)) {
                rmdir($file);
            }
        }
    }
}



