<?php
namespace DemoApp;
use CLIFramework\Testing\CommandTestCase;

class HelpCommandTest extends CommandTestCase
{

    public static function setupApplication() {
        return new \DemoApp\Application;
    }

    public function testHelpCommand() {
        $this->expectOutputRegex("/A simple demo command/");
        $this->assertTrue( $this->runCommand('example/demo help') );
    }

    public function testHelpTopicCommand() {
        $this->expectOutputRegex("/A bare repository is normally an appropriately/");
        $this->assertTrue( $this->runCommand('example/demo help basic') );
    }

}



