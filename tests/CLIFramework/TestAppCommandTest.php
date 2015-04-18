<?php
use CLIFramework\ArgInfo;
use TestApp\Application;

class TestAppCommandTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleCommand()
    {
        $command = new TestApp\Command\SimpleCommand(new Application);

        $argInfos = $command->getArgumentsInfo();
        $this->assertNotEmpty($argInfos);
        $this->assertCount(1, $argInfos);
        $this->assertEquals('var', $argInfos[0]->name);
    }

    public function testArginfoCommand() {
        $cmd = new TestApp\Command\ArginfoCommand(new Application);
        $argInfos = $cmd->getArgumentsInfo();
        $this->assertNotEmpty($argInfos);
        $this->assertCount(3, $argInfos);

        foreach( $argInfos as $arginfo ) {
            $this->assertInstanceOf('CLIFramework\ArgInfo',  $arginfo);
        }
    }
}

