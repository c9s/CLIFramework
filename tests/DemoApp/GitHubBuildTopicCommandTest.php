<?php
namespace DemoApp;
use CLIFramework\Testing\CommandTestCase;

class GitHubBuildTopicCommandTest extends CommandTestCase
{

    public function setupApplication() {
        return new \DemoApp\Application;
    }

    public function testBuildGitHubTopics()
    {
        $this->expectOutputRegex("!Creating src/PHPBrew/Topic/ContributionTopic.php!");
        $this->runCommand('example/demo _build-github-wiki --ns PHPBrew:Topic --dir src phpbrew phpbrew');
    }
}



