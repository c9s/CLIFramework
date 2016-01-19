<?php
namespace CLIFramework\Exception;
use Exception;
use CLIFramework\CommandBase;
use CLIFramework\Exception\CommandBaseException;

class CommandNotFoundException extends CommandBaseException
{
    public $name;

    public function __construct(CommandBase $command, $name) {
        $this->name = $name;
        parent::__construct($command, "Command $name not found.");
    }
}


