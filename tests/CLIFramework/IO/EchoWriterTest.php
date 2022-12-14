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

use CLIFramework\IO\EchoWriter;
use PHPUnit\Framework\TestCase;

class EchoWriterTest extends TestCase
{
    private $writer;

    protected function setUp(): void
    {
        $this->writer = new EchoWriter();
    }

    function testWrite()
    {
        $this->expectOutputString("test");
        $this->writer->write("test");
    }

    function testWriteln()
    {
        $this->writer->writeln("test");
        $this->expectOutputString("test\n");
    }

    function testWritef()
    {
        $this->writer->writef("%s:%s", "test", "writef");
        $this->expectOutputString("test:writef");
    }
}
