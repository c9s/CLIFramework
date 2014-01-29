<?php
namespace CLIFramework\Exception;
use Exception;

class CommandNotFoundException extends Exception
{
    public function __construct($name) {
        parent::__construct("Command $name not found.");
    }
}


