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

class LoggerTest extends PHPUnit_Framework_TestCase 
{
    function testColoredOutput()
    {
        $logger = new \CLIFramework\Logger;
        $logger->info('test');
        $logger->debug('test');

        $this->expectOutputString("\033[2mtest\033[0m\n");
    }

    function testRawOutput()
    {
        $logger = new \CLIFramework\Logger;
        $logger->getFormatter()->preferRawOutput();
        $logger->info('test');
        $logger->debug('test');

        $this->expectOutputString("test\n");
    }


}
