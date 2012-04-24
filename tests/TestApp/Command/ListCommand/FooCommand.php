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
namespace TestApp\Command\ListCommand;
use CLIFramework\Command;

/** Test process stage **/
class FooCommand extends Command
{

    function prepare()
    {
        global $_prepare;
        $_prepare = 1;
    }

    function execute()
    {
        global $_execute;
        $_execute = 1;
    }

    function finish()
    {
        global $_finish;
        $_finish = 1;
    }
}


