<?php
namespace DemoApp;
use CLIFramework\Testing\CommandTestCase;
use CLIFramework\ServiceContainer;

class MetaCommandTest extends CommandTestCase
{

    public static function setupApplication()
    {
        $service = new ServiceContainer;
        $service['logger']->setQuiet();
        $app = new \DemoApp\Application($service);
        return $app;
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
        $this->assertTrue( $this->runCommand('example/demo meta --zsh commit arg 1 valid-values'));
    }

    public function testOptValidValues() {
        ob_start();
        $this->assertTrue( $this->runCommand('example/demo meta --zsh commit opt reuse-message valid-values'));
        $output = ob_get_contents();
        ob_end_clean();
        $lines = explode("\n",trim($output));

        $this->assertEquals('#values',$lines[0]);
        array_shift($lines);
        foreach($lines as $line) {
            $this->assertRegExp('/^\w{7}$/', $line);
        }
    }

    public function testGenerateZshCompletion() {
        $this->expectOutputRegex("!compdef _demo demo!");
        $this->assertTrue( $this->runCommand('example/demo zsh --program demo --bind demo') );
    }

    /**
     * @expectedException CLIFramework\Exception\CommandNotFoundException
     */
    public function testCommandNotFound()
    {
        $this->assertTrue( $this->runCommand('example/demo --no-interact zzz') );
    }

    /**
     * @expectedException CLIFramework\Exception\CommandArgumentNotEnoughException
     */
    public function testArgument()
    {
        $this->assertTrue( $this->runCommand('example/demo commit') );
    }

}

