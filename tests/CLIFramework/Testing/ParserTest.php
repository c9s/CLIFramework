<?php
namespace tests\CLIFramework\Testing;

use CLIFramework\Testing\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testGetArguments_OneArgument()
    {
        $command = "program arg1";
        $expect = array("program", "arg1");

        $result = Parser::getArguments($command);
        $this->assertEquals($expect, $result);
    }

    public function testGetArguments_TwoArguments()
    {
        $command = "program arg1    arg2";
        $expect = array("program", "arg1", "arg2");

        $result = Parser::getArguments($command);
        $this->assertEquals($expect, $result);
    }

    public function testGetArguments_ArgumentWithSpaces()
    {
        $command = "program arg1 \"arg2.1 arg2.2\" arg3";
        $expect = array("program", "arg1", "arg2.1 arg2.2", "arg3");

        $result = Parser::getArguments($command);
        $this->assertEquals($expect, $result);
    }
}
?>
