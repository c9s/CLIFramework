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
use CLIFramework\ServiceContainer;
use PHPUnit_Framework_TestCase;

class DaemonExtensionTest extends PHPUnit_Framework_TestCase 
{
    private $extension;

    private $command;

    public function setUp()
    {
        $extension = new DaemonExtensionForTest;
        if (!$extension->isAvailable()) {
            $this->markTestSkipped('DaemonExtension is not available.');
        }

        $this->command = new DaemonExtensionTestCommand();

        // Setup a new application
        $this->command->setApplication(new Application());
        $this->command->_init();
    }

    public function testRun()
    {
        $this->command->executeWrapper(array());
    }

    public function tearDown()
    {
    }
}

class DaemonExtensionForTest extends DaemonExtension
{
    protected $detach = false;
}

class DaemonExtensionTestCommand extends Command
{
    public function init()
    {
        $this->extension(new DaemonExtensionForTest);
    }

    public function execute()
    {
    }
}
