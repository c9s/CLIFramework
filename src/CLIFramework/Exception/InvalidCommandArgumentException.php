<?php
namespace CLIFramework\Exception;
use Exception;

class InvalidCommandArgumentException extends Exception
{
    public $command;

    public $argIndex;

    public function __construct($command, $argIndex) {
        $this->command = $command;
        $this->argIndex = $argIndex;
        parent::__construct();
    }
}

