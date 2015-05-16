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
namespace tests\CLIFramework\Extension;

use CLIFramework\Extension\DaemonExtension;
use CLIFramework\Command;

class DaemonExtensionTest extends \PHPUnit_Framework_TestCase 
{
    private $extension;
    private $command;

    function setUp()
    {
        if (!DaemonExtension::isAvailable()) {
            $this->markTestSkipped('DaemonExtension is not available.');
        }
        $this->extension = new DaemonExtension();
        $this->extension->noClose();
        $this->command = new DaemonExtensionTestCommand();
        $this->extension->bind($this->command);
    }

    function tearDown()
    {
        $this->command->callHook('execute.after');
        $this->assertFalse(file_exists($this->extension->getPidFilePath()));
    }

    function testCallHookBeforeRun()
    {
        $isSuccess = false;

        $this->extension->addHook('run.before', function() use (&$isSuccess) {
            $isSuccess = true;
        });

        $this->extension->run();

        $this->assertTrue($isSuccess);
    }

    function testCallHookAfterRun()
    {
        $isSuccess = false;

        $this->extension->addHook('run.after', function() use (&$isSuccess) {
            $isSuccess = true;
        });

        $this->extension->run();

        $this->assertTrue($isSuccess);
    }

    function testBind()
    {
        $this->assertFalse(file_exists($this->extension->getPidFilePath()));
        $this->command->callHook('execute.before');
        $this->assertTrue(file_exists($this->extension->getPidFilePath()));
    }

    function testRun()
    {
        $pid = getmypid();
        $this->extension->run();
        $this->assertTrue($pid !== getmypid());
    }
}

class DaemonExtensionTestCommand extends Command
{
    public function execute()
    {
    }
}
