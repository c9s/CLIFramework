<?php
use CLIFramework\ArgInfo;
use TestApp\Application;

class TestAppCommandTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleCommand()
    {
        $command = new TestApp\Command\SimpleCommand(new Application);
        $command->_init();

        $argInfos = $command->getArgInfoList();
        $this->assertNotEmpty($argInfos);
        $this->assertCount(1, $argInfos);
        $this->assertEquals('var', $argInfos[0]->name);
    }

    public function testArginfoCommand() {
        $cmd = new TestApp\Command\ArginfoCommand(new Application);
        $cmd->_init();

        $argInfos = $cmd->getArgInfoList();
        $this->assertNotEmpty($argInfos);
        $this->assertCount(3, $argInfos);

        foreach( $argInfos as $arginfo ) {
            $this->assertInstanceOf('CLIFramework\ArgInfo',  $arginfo);
        }
    }
}

