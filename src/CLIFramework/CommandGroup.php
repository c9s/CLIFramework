<?php
namespace CLIFramework;
use CLIFramework\Command;

class CommandGroup
{
    public $name;

    public $commandNames = array();

    public function __construct($groupName, $commands = array())
    {
        $this->name = $groupName;
        $this->commands = $commands;
    }

    public function addCommand($commandName) {
        $this->commands[] = $commandName;
    }

    public function getCommands() {
        return $this->commands;
    }
}


