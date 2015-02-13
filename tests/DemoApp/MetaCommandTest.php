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
        $this->runCommand('example/demo meta --zsh commit arg 0 valid-values');
    }

    public function testMetaArgSimpleValidValues()
    {
        $this->expectOutputString("#values
CLIFramework
GetOptionKit
PHPBrew
AssetKit
ActionKit
");
        ok( $this->runCommand('example/demo meta --zsh commit arg 1 valid-values'));
    }

    public function testOptValidValues() {
        ob_start();
        ok( $this->runCommand('example/demo meta --zsh commit opt reuse-message valid-values'));
        $output = ob_get_contents();
        ob_end_clean();
        $lines = explode("\n",trim($output));

        is('#values',$lines[0]);
        array_shift($lines);
        foreach($lines as $line) {
            like('/^\w{7}$/', $line);
        }
    }

    public function testGenerateZshCompletion() {
        $this->expectOutputRegex("!compdef _demo demo!");
        ok( $this->runCommand('example/demo zsh --program demo --bind demo') );
    }

    public function testCommandNotFound() {
        $this->setExpectedException('CLIFramework\\Exception\\CommandNotFoundException');
        ok( $this->runCommand('example/demo --no-interact zzz') );
    }

    public function testArgument() {
        $this->setExpectedException('CLIFramework\\Exception\\CommandArgumentNotEnoughException');
        ok( $this->runCommand('example/demo commit') );
    }

}

