<?php
use CLIFramework\ReadLine;

class ReadlineTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $rl = new ReadLine;
        ok($rl);
    }
}

