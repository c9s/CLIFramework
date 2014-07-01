<?php
namespace CLIFramework\Exception;
use Exception;

class CommandNotFoundException extends Exception
{
    public $name;

    public function __construct($name) {
        $this->name = $name;
        parent::__construct("Command $name not found.");
    }
}


