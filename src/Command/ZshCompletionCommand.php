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
use CLIFramework\Completion\ZshGenerator;

#[\AllowDynamicProperties]
class ZshCompletionCommand extends Command implements CommandInterface
{
    public function brief()
    {
        return 'This function generate a zsh-completion script automatically';
    }

    public function options($opts)
    {
        $opts->add('bind:', 'bind complete to command');
        $opts->add('program:', 'programe name');
    }

    public function execute()
    {
        $programName = $this->options->program ?: $this->getApplication()->getProgramName();
        $bind = $this->options->bind ?: $programName;
        $compName = '_'.preg_replace('#\W+#', '_', $programName);
        $generator = new ZshGenerator($this->getApplication(), $programName, $bind, $compName);
        echo $generator->output();
    }
}
