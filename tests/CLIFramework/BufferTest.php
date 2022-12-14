<?php
use CLIFramework\Buffer;
use PHPUnit\Framework\TestCase;

class BufferTest extends TestCase
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

