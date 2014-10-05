<?php
namespace CLIFramework\Exception;
use Exception;
use CLIFramework\CommandBase;

class CommandArgumentNotEnoughException extends CommandBaseException
{
    public $given;

    public $required;

    public function __construct(CommandBase $command, $given, $required) {
        $this->given = $given;
        $this->required = $required;
        parent::__construct($command, "Insufficient arguments for command '{$command->getName()}', which requires $required arguments, $given given.");
    }


}
