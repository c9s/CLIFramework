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

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $app = new Application;
        ok( $app );

        $argv = explode(' ','app -v -d list foo arg1 arg2 arg3');
        $app->run($argv);


        global $_prepare;
        global $_execute;
        global $_finish;
        ok( $_prepare );
        ok( $_execute );
        ok( $_finish );
    }
}
