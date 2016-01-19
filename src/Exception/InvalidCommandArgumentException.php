<?php
namespace CLIFramework\Exception;
use Exception;
use CLIFramework\Exception\CommandBaseException;
use CLIFramework\CommandBase;

class InvalidCommandArgumentException extends CommandBaseException
{
    public $arg;

    public $argIndex;

    public function __construct(CommandBase $command, $argIndex, $arg) {
        $this->argIndex = $argIndex;
        $this->arg = $arg;
        parent::__construct($command, "Invalid '{$command->getName()}' command argument '$arg' at position $argIndex");
    }
}

