<?php
namespace CLIFramework\ReadLine\Completer;

interface Completer {

    public function complete($lastToken, $index);

}

