<?php
namespace CLIFramework\ConsoleInfo;

class EnvConsoleInfo implements ConsoleInfoInterface
{
    public function getColumns() 
    {
        return intval(getenv('COLUMNS'));
    }

    public function getRows() 
    {
        return intval(getenv('LINES'));
    }

    static public function hasSupport()
    {
        return getenv('COLUMNS') && getenv('LINES');
    }
}



