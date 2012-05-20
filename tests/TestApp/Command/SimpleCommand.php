<?php
namespace TestApp\Command;
use CLIFramework\Command;
use Exception;

class SimpleCommand extends Command
{
    public function execute($var)
    {
        return $var;
    }
}




