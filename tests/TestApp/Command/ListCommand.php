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
namespace TestApp\Command;

use CLIFramework\Command;

class ListCommand extends Command 
{

    function brief()
    {
        return 'brief message';
    }

    function usage()
    {
        return 'app list [arguments]';
    }

    function init()
    {
        $this->registerCommand('foo');
        $this->registerCommand('extra', 'TestApp\Command\ListCommand\ExtraArgumentTestCommand');
    }

    function execute()
    {

    }

}

