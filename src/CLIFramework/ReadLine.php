<?php
namespace CLIFramework;

class ReadLine
{

    public $completers = array();

    public function registerCompleter($completer) {
        $this->completers[] = $completer;
    }


    public function complete($token, $index) {
        $info = readline_info();
        $fullInput = substr($info['line_buffer'], 0, $info['end']);

        $matches = array();
        foreach( $this->completers as $completer ) {
            if ($completer->canComplete($input, $token, $index)) {
                $_matches = $completer->complete($input, $token, $index);
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


