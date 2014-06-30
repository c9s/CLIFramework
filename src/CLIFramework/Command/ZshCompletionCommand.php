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

  _arguments -C \
    '1:cmd:->cmds' \
    '*::arg:->args' \
  && ret=0

  case "\$state" in
    (cmds)
{$cmdsDescs}
    ;;
    (args)
      case \$words[1] in

HEREDOC;

        // generate subcommand section
        /*
        (browse)
          _arguments \
            '1: :_github_users' \
            '2: :_github_branches' \
          && ret=0
        ;;
        */
        foreach ($cmds as $k => $cmd) {
            $_args  = Zsh::indent_array(Zsh::command_args($cmd), 3);

            // XXX: support alias
            $_flags = Zsh::indent_array(Zsh::command_flags($cmd), 3);
            $_code = '';
            $_code .= "(" . $k . ")\n";

            /* TODO: get argument spec from command class -> execute method 
             * to the argument spec below:
             *
                _arguments \
                    '1: :_github_users' \
                    '2: :_github_branches' \
                    && ret=0
            */
            if (!empty($_flags) || !empty($_args) ) {
                $_code .= "  _arguments -s -w : \\\n";

                if (!empty($_args))
                    $_code .= join(" \\\n",$_args) . "\\\n";

                if (!empty($_flags))
                    $_code .= join(" \\\n",$_flags) . "\\\n";

                $_code .= "    && ret=0\n";
            }

            $_code .= "   ;;\n";
            $code .= Zsh::indent_str($_code,2);
        }


        $code .=<<<HEREDOC
      esac
    ;;
  esac
  return ret
}

compdef $compName $programName

HEREDOC;




        echo $code;
    }




}
