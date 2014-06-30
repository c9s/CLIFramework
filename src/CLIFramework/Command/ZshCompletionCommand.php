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

        /* for debug
            $programName = 'foo';
            $compName = '_foo';
         */

        // var_dump( $argv ); 
        $code = "";
        $code .= "# THIS IS AN AUTO-GENERATED FILE, PLEASE DON'T MODIFY THIS FILE DIRECTLY.\n";


        $app = $this->getApplication();
        $cmds = Zsh::visible_commands($app->getCommandObjects());

        $cmdsDescs  = Zsh::describe_commands($cmds);
        $cmdsDescs  = Zsh::indent_str($cmdsDescs, 3);


        $comp = Zsh::complete_subcommands($app);
        /*
        (create)
          _arguments \
            '1:repo name' \
            '--markdown[create README.markdown]' \
            '--mdown[create README.mdown]' \
            '--private[create private repository]' \
            '--rdoc[create README.rdoc]' \
            '--rst[create README.rst]' \
            '--textile[create README.textile]' \
          && ret=0
        ;;
        */


        $code .=<<<HEREDOC
{$compName}() {
  typeset -A opt_args
  local context state line curcontext="\$curcontext"
  local ret=1
  $comp
  return ret
}

compdef $compName $programName

HEREDOC;




        echo $code;
    }




}
