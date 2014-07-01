<?php
namespace CLIFramework\Exception;
use Exception;

class CommandArgumentNotEnoughException extends Exception
{
    public $command;

    public $given;

    public $required;

    public function __construct($command, $given, $required) {
        $this->command = $command;
        $this->given = $given;
        $this->required = $required;
        parent::__construct("Insufficient arguments for command '{$command->getName()}', which requires $required arguments, $given given.");
    }
}
