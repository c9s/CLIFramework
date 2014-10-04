<?php
namespace CLIFramework\Exception;
use Exception;
use CLIFramework\CommandBase;

class CommandNotFoundException extends Exception
{
    public $name;

    public $command;

    public function __construct(CommandBase $command, $name) {
        $this->command = $command;
        $this->name = $name;
        parent::__construct("Command $name not found.");
    }

    public function getCommand() {
        return $this->command;
    }
}


