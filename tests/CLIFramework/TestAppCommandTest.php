<?php
use CLIFramework\ArgumentInfo;

class TestAppCommandTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleCommand()
    {
        $command = new TestApp\Command\SimpleCommand(new TestApp\Application);
        ok($command);

        $argInfos = $command->getArgumentsInfo();
        ok($argInfos);

        count_ok(1, $argInfos);
        is('var', $argInfos[0]->name);
    }

    public function testArginfoCommand() {

        $cmd = new TestApp\Command\ArginfoCommand(new TestApp\Application);
        ok($cmd);
        $argInfos = $cmd->getArgumentsInfo();
        ok($argInfos);
        count_ok(3, $argInfos);

        foreach( $argInfos as $arginfo ) {
            ok( $arginfo instanceof ArgumentInfo);
        }
    }
}

