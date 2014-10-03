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

    public function brief() {
        return 'brief message';
    }

    public function usage() {
        return 'app list [arguments]';
    }

    public function init() {
        $this->command('foo');
        $this->command('extra', 'TestApp\Command\ListCommand\ExtraArgumentTestCommand');
    }

    public function execute() {

    }

}

