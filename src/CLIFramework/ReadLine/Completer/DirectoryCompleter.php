<?php
namespace CLIFramework\ReadLine\Completer;

class DirectoryCompleter implements Completer
{

    public function canComplete($input, $token, $index) {
        return true;
    }

    public function complete($input, $token, $index) {
        return array();
    }

}

