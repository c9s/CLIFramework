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
use CLIFramework\Application;
use PHPUnit_Framework_TestCase;

class DaemonExtensionTest2 extends \PHPUnit_Framework_TestCase 
{
    private $extension;
    private $command;

    public function setUp()
    {
        if (!DaemonExtension::isAvailable()) {
            $this->markTestSkipped('DaemonExtension is not available.');
        }
        $this->extension = new DaemonExtensionForTest();
        $this->extension->noDetach();
        $this->command = new DaemonExtensionTestCommand();
        $this->command->setApplication(new Application());
        $this->command->_init();
        $this->extension->bind($this->command);
    }

    public function testRun()
    {
        $this->assertTrue(true);
    }

    public function tearDown()
    {
        $this->assertFalse(file_exists($this->extension->getPidFilePath()));
    }
}

class DaemonExtensionForTest extends DaemonExtension
{
    public function noDetach()
    {
        parent::noDetach();
    }
}

class DaemonExtensionTestCommand extends Command
{
    public function execute()
    {
    }
}
