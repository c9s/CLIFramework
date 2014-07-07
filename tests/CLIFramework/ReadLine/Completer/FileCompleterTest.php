<?php
use CLIFramework\ReadLine\Completer\FileCompleter;

class FileCompleterTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $completer = new FileCompleter;
        ok($completer);
    }
}

