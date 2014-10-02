<?php
namespace CLIFramework;
use CLIFramework\Command;

class CommandGroup
{
    public $name;

    public $commandNames = array();

    public function __construct($groupName, $commandNames = array())
    {
        $this->name = $groupName;
        $this->commandNames = $commandNames;
    }

    public function addCommand($commandName) {
        $this->commandNames[] = $commandName;
    }
}


