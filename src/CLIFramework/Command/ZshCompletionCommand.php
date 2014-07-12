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
namespace CLIFramework\Command;

use CLIFramework\Command;
use CLIFramework\CommandInterface;
use CLIFramework\Zsh;

class ZshCompletionCommand extends Command
    implements CommandInterface
{

    public function brief() { return 'This function generate a zsh-completion script automatically'; }

    public function execute($as = null) {
        global $argv;

        if ($as) {
            $programName = $as;
        } else {
            $programName = $argv[0];
        }

        $compName = "_" . preg_replace('#\W+#','_',$programName);
        echo Zsh::complete_application($this->getApplication(), $programName, $compName);
    }




}
