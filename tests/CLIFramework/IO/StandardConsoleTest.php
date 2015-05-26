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

use CLIFramework\IO\StandardConsole;
use CLIFramework\Testing\ConsoleTestCase;

class StandardConsoleTest extends ConsoleTestCase
{
    function testReadLine()
    {
        $script = __DIR__ . '/../../script/CLIFramework/IO/StandardConsoleReadLine.php';
        $self = $this;
        $this->runScript($script, "test\n", function($line) use($self) {
            $self->assertSame('test', $line);
        });
    }

    function testReadPassword()
    {
        $script = __DIR__ . '/../../script/CLIFramework/IO/StandardConsoleReadPassword.php';
        $self = $this;
        $this->runScript($script, "test\n", function($line) use($self) {
            $self->assertSame('test', $line);
        });
    }
}
