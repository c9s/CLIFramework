<?php
namespace tests\CLIFramework;

use CLIFramework\Command;
use CLIFramework\Application;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    private $command;

    protected function setUp(): void
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
