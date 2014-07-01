<?php
namespace CLIFramework\Exception;
use Exception;

class InvalidCommandArgumentException extends Exception
{
    public $command;

    public $arg;

    public $argIndex;

    public function __construct($command, $argIndex, $arg) {
        $this->command = $command;
        $this->argIndex = $argIndex;
        $this->arg = $arg;
        parent::__construct("Invalid '{$command->getName()}' command argument '$arg' at position $argIndex");
    }
}

