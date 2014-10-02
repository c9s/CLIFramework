<?php
namespace CLIFramework;
use CLIFramework\Command;

class CommandGroup
{
    public $name;

    public $desc;

    public $commandNames = array();

    public function __construct($groupName, $commands = array())
    {
        $this->name = $groupName;
        $this->commands = $commands;
    }

    public function addCommand($commandName) {
        $this->commands[] = $commandName;
        return $this;
    }

    public function getCommands() {
        return $this->commands;
    }


    /**
     * Set group description
     *
     * @param string $desc
     * @return CommandGroup
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
        return $this;
    }

    /**
     * Get the group description
     */
    public function getDesc() {
        return $this->desc;
    }
}


