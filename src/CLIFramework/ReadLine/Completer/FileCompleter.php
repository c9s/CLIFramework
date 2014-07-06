<?php
namespace CLIFramework\ReadLine\Completer;

class FileCompleter implements Completer
{

    public function complete($lastToken, $index) {
        $info = readline_info();
        $fullInput = substr($info['line_buffer'], 0, $info['end']);

        // If the user is typing: 
        // mv file.txt directo[TAB] 
        // then: 
        // $lastToken = directo 
        // the $index is the place of the cursor in the line: 
        // $index = 19; 
        $array = array( 
            'ls', 
            'mv', 
            'dar', 
            'exit', 
            'quit', 
        ); 

        // Here, I decide not to return filename autocompletion for the first argument (0th argument). 
        if ($index) { 
            $ls = `ls`; 
            $lines = explode("\n", $ls); 
            foreach ($lines AS $key => $line) { 
            if (is_dir($line)) { 
                $lines[$key] .= '/'; 
            } 
            $array[] = $lines[$key]; 
            } 
        } 
        // This will return both our list of functions, and, possibly, a list of files in the current filesystem. 
        // php will filter itself according to what the user is typing. 
        return $array; 
    }
}


