<?php
namespace DemoApp;
use CLIFramework\Testing\CommandTestCase;

class MetaCommandTest extends CommandTestCase
{

    public function setupApplication() {
        return new \DemoApp\Application;
    }

    public function testMetaArgValidValuesGroups()
    {
        $this->expectOutputRegex("/#groups/");
        $this->runCommand('example/demo _meta --zsh commit arg 0 valid-values');
    }

    public function testMetaArgSimpleValidValues()
    {
        $this->expectOutputString("#values
CLIFramework
GetOptionKit
");
        $this->runCommand('example/demo _meta --zsh commit arg 1 valid-values');
    }

    public function testHelpCommand() {
        $this->expectOutputRegex("/A simple demo command/");
        $this->runCommand('example/demo help');
    }

    public function testHelpTopicCommand() {
        $this->expectOutputRegex("/A bare repository is normally an appropriately/");
        $this->runCommand('example/demo help basic');
    }

    public function testGenerateZshCompletion() {
        $this->expectOutputRegex("!compdef _demo demo!");
        $this->runCommand('example/demo _zsh --program demo --bind demo');
    }

}

