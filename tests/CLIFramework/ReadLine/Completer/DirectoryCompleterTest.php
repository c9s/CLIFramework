<?php
use CLIFramework\ReadLine\Completer\DirectoryCompleter;

class DirectoryCompleterTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $completer = new DirectoryCompleter;
        ok($completer);
    }
}

