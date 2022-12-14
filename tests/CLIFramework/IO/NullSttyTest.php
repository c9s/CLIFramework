<?php
/*
 * This file is part of the CLIFramework package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace tests\CLIFramework\IO;

use CLIFramework\IO\NullStty;
use PHPUnit\Framework\TestCase;

class NullSttyTest extends TestCase
{
    private $stty;

    protected function setUp(): void
    {
        $this->stty = new NullStty();
    }

    function testEnableEcho()
    {
        $this->stty->enableEcho();
    }

    function testDisableEcho()
    {
        $this->stty->disableEcho();
    }

    function testDump()
    {
        $this->assertSame('', $this->stty->dump());
    }

    function testWithoutEcho()
    {
        $this->assertSame('echo', $this->stty->withoutEcho(function() {
            return 'echo';
        }));
    }
}

