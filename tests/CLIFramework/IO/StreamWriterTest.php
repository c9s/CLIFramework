<?php
/*
 * This file is part of the {{ }} package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace tests\CLIFramework\IO;

use CLIFramework\IO\StreamWriter;
use PHPUnit\Framework\TestCase;

class StreamWriterTest extends TestCase
{
    private $writer;
    private $stream;

    protected function setUp(): void
    {
        $this->stream = fopen('php://memory', 'rw');
        $this->writer = new StreamWriter($this->stream);
    }

    protected function tearDown(): void
    {
        fclose($this->stream);
    }

    function testWrite()
    {
        $this->writer->write("test");
        $this->assertStreamSame("test");
    }

    function testWriteln()
    {
        $this->writer->writeln("test");
        $this->assertStreamSame("test\n");
    }

    function testWritef()
    {
        $this->writer->writef("%s:%s", "test", "writef");
        $this->assertStreamSame("test:writef");
    }

    function assertStreamSame($expected)
    {
        fseek($this->stream, 0);
        $this->assertSame($expected, stream_get_contents($this->stream));
    }
}
