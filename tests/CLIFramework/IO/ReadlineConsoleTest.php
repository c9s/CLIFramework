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

use CLIFramework\IO\ReadlineConsole;
use CLIFramework\Testing\ConsoleTestCase;

class ReadlineConsoleTest extends ConsoleTestCase
{
    function testReadLine()
    {
        if (!ReadlineConsole::isAvailable()) {
            $this->markTestSkipped('readline is not available.');
        }

        $script = __DIR__ . '/../../script/CLIFramework/IO/ReadlineConsoleReadLine.php';
        $self = $this;
        $this->runScript($script, "test\n", function($line) use($self) {
            $self->assertSame('test', $line);
        });
    }

    function testReadPassword()
    {
        if (!ReadlineConsole::isAvailable()) {
            $this->markTestSkipped('readline is not available.');
        }

        $script = __DIR__ . '/../../script/CLIFramework/IO/ReadlineConsoleReadPassword.php';
        $self = $this;
        $this->runScript($script, "test\n", function($line) use($self) {
            $self->assertSame('test', $line);
        });
    }
}
