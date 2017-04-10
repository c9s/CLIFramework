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
namespace tests\CLIFramework;
use TestApp\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function test()
    {
        $app = new Application;

        $argv = explode(' ','app -v -d list foo arg1 arg2 arg3');
        $ret = $app->run($argv);
        $this->assertTrue($ret);

        $logger = $app->getLogger();

        global $_prepare;
        global $_execute;
        global $_finish;
        $this->assertNotNull( $_prepare );
        $this->assertNotNull( $_execute );
        $this->assertNotNull( $_finish );
    }


    function testOptionParsing()
    {
        $app = new Application;
        $argv = explode(' ','app -v -d test1 --as AS ARG1 ARG2');
        $ret = $app->run($argv);
        $this->assertTrue( $ret );
    }

    function testExtraArguments()
    {
        $app = new Application;
        $argv = explode(' ','app -v -d list extra --as AS ARG1 ARG2');
        $ret = $app->run($argv);
        $this->assertTrue( $ret );
    }

}
