<?php
use CLIFramework\ArgInfo;
use PHPUnit\Framework\TestCase;

class ArgInfoTest extends TestCase
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

