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
    function test()
    {
        $logger = new \CLIFramework\Logger;
        $this->assertNotEmpty( $logger );

        ob_start();
        $logger->info('test');
        $logger->debug('test');
        ob_clean();
    }


}
