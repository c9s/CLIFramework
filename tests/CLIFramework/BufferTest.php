<?php
use CLIFramework\Buffer;
use PHPUnit\Framework\TestCase;


class BufferTest extends TestCase
{
    public function testAppend()
    {
        $buf = new Buffer;
        $buf->appendLine('foo');
        $buf->appendLine('bar');

        $buf->indent();
        $buf->appendLine('inner bar');
        $buf->unindent();
        $buf->unindent();
        $this->assertEquals(0, $buf->indent);

        $this->assertNotNull($buf->__toString());
    }
}

