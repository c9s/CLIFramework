<?php
namespace CLIFramework\Exception;
use Exception;
use CLIFramework\CommandBase;

class CommandBaseException extends Exception
{
    public $command;

    public function __construct(CommandBase $command, $message = "", $code = 0, $previous = NULL) {
        $this->command = $command;
        parent::__construct($message, $code, $previous);
    }

    public function getCommand() {
        return $this->command;
    }
}



