<?php
namespace CLIFramework\ConsoleInfo;

interface ConsoleInfoInterface { 
    public function getColumns();
    public function getRows();
    static public function hasSupport();
}


