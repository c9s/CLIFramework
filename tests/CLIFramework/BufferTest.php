<?php
use CLIFramework\Buffer;

class BufferTest extends PHPUnit_Framework_TestCase
{
    public function testAppend()
    {
        $buf = new Buffer;
        ok($buf);

        $buf->appendLine('foo');
        $buf->appendLine('bar');

        $buf->indent();
        $buf->appendLine('inner bar');
        $buf->unindent();
        $buf->unindent();
        is(0, $buf->indent);

        ok($buf->__toString());
    }
}

