<?php
namespace CLIFramework;
use CLIFramework\Command;
use CLIFramework\CommandBase;

class CommandGroup
{
    public $id;

    public $name;

    public $desc;

    public $commands = array();

    public $isHidden = false;

    public function __construct($groupName, $commands = array())
    {
        $this->name = $groupName;
        $this->commands = $commands;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id ?: $this->getName();
    }

    public function getName() {
        return $this->name;
    }

    public function addCommand($name, CommandBase $command) {
        $this->commands[$name] = $command;
        return $this;
    }

    public function getCommands() {
        return $this->commands;
    }

    public function getCommandNames() {
        return array_keys($this->commands);
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

    public function hidden() {
        $this->isHidden = true;
        return $this;
    }
}


