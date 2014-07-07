<?php
namespace CLIFramework;

class ReadLine
{

    public $autoCompleters = array();

    public function registerAutoCompleter($autoCompleter) {
        $this->autoCompleters[] = $autoCompleter;
    }


    public function complete($token, $index) {
        $info = readline_info();
        $fullInput = substr($info['line_buffer'], 0, $info['end']);

        $matches = array();
        foreach( $this->autoCompleters as $completer ) {
            if ($completer->canComplete($token, $index)) {
                $_matches = $completer->complete($token, $index);
                foreacH ($_matches as $_m) {
                    $matches[] = $m;
                }
            }
        }
        return $matches;
    }



    public function readline($prompt = '> ') {
        $success = readline_completion_function( array($this,"complete") );
        $ret = readline($prompt);
        return $ret;
    }

}


