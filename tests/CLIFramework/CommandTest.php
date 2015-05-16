<?php
namespace tests\CLIFramework;

use CLIFramework\Command;
use CLIFramework\Application;

class CommandTest extends \PHPUnit_Framework_TestCase
{
    private $command;

    public function setUp()
    {
        $this->command = new CommandTestCommand();
    }

    public function testHasApplicationWhenApplicationIsNotSet()
    {
        $this->assertFalse($this->command->hasApplication());
    }

    public function testHasApplicationWhenApplicationIsSet()
    {
        $this->command->setApplication(new Application);
        $this->assertTrue($this->command->hasApplication());
    }
}

class CommandTestCommand extends Command
{
    public function execute()
    {
    }
}
