<?php

class TestAppCommandTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleCommand()
    {
        $command = new TestApp\Command\SimpleCommand(new TestApp\Application);
        ok($command);

        $argInfos = $command->getArgumentsInfo();
        ok($argInfos);


    }
}

