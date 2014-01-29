<?php
namespace CLIFramework\Exception;
use Exception;

class CommandNotFoundException extends Exception
{
    public function __construct($class) {
        parent::__construct("Command class $class not found.");
    }
}


