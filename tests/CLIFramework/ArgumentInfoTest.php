<?php
use CLIFramework\ArgumentInfo;

class ArgumentInfoTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $info = new ArgumentInfo('user');
        ok($info);

        $info->type('number');
        ok($info->test('123'));

        ok(!$info->test('foo'));
    }
}

