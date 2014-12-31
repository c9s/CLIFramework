<?php
namespace CLIFramework\ConsoleInfo;

class TputConsoleInfo
{

    public function getColumns() { 
        return intval(exec('tput cols'));
    }

    public function getRows() {
        return intval(exec('tput lines'));
    }

}



