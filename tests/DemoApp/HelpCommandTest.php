<?php
namespace DemoApp;
use CLIFramework\Testing\CommandTestCase;

class HelpCommandTest extends CommandTestCase
{

    public function setupApplication() {
        return new \DemoApp\Application;
    }

    public function testHelpCommand() {
        $this->expectOutputRegex("/A simple demo command/");
        ok( $this->runCommand('example/demo help') );
    }

    public function testHelpTopicCommand() {
        $this->expectOutputRegex("/A bare repository is normally an appropriately/");
        ok( $this->runCommand('example/demo help basic') );
    }

}



