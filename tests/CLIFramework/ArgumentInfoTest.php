<?php
use CLIFramework\ArgInfo;
use PHPUnit\Framework\TestCase;


class ArgInfoTest extends TestCase
{
    public function test()
    {
        $info = new ArgInfo('user');
        $info->isa('number');
        $this->assertTrue($info->validate('123'));

        $this->assertFalse($info->validate('foo'));
    }
}

