<?php
use CLIFramework\ArgInfo;

class ArgInfoTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $info = new ArgInfo('user');
        ok($info);

        $info->isa('number');
        ok($info->validate('123'));

        ok(!$info->validate('foo'));
    }
}

