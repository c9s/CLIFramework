<?php
namespace TestApp\Command;
use CLIFramework\Command;
use Exception;

class SimpleCommand extends Command
{

    function help()
    {
        return <<<HELP
<info>Info Style</info>

<bold>Bold Text</bold>
<underline>Bold Text</underline>
HELP;
    }

    function execute($var)
    {
        return $var;
    }
}




