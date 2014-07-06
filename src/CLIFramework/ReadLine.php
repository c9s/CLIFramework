<?php
namespace CLIFramework;

class ReadLine
{

    public $autoCompleters = array();

    public function registerAutoCompleter($autoCompleter) {
        $this->autoCompleters[] = $autoCompleter;
    }

}


