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

function indent_str($content, $level = 1) {
    $space = str_repeat('  ', $level);
    $lines = explode("\n", $content);
    $lines = array_map(function($line) use ($space) { return $space . $line; }, $lines);
    return join("\n", $lines);
}

function zsh_command_desc_item($name, $cmd) {
    return "'$name:" . addslashes($cmd->brief()) . "'";
}

function zsh_command_desc_array($cmds) {
    $code = "local commands; commands=(\n";
    foreach ( $cmds as $name => $cmd ) {
        $code .= "  " . zsh_command_desc_item($name, $cmd) . "\n";
    }
    $code .= ")\n";
    return $code;
}


/**
 *
 *
 * Generate an zsh option format like this:
 *
 *  '(-v --invert-match)'{-v,--invert-match}'[invert match: select non-matching lines]'
 *
 * Or:
 *
    '-gcflags[flags for 5g/6g/8g]:flags'
    '-p[number of parallel builds]:number'
 *
 *
 */
function zsh_option_flag_item($opt) {
    // TODO: Check conflict options
    $str = '';
    if ($opt->short && $opt->long) {
        $str .= '{' . '-' . $opt->short . ',' . '--' . $opt->long . "}" . "'";
    } else if ($opt->long) {
        $str .= "'--" . $opt->long;
    } else if ($opt->short) {
        $str .= "'-" . $opt->short;
    }
    $str .= "[" . $opt->description . "]";
    if ($opt->valueType) {
        $str .= ":" . $opt->valueType;
    }
    $str .= "'";
    return $str;
}


/**
 * Return the zsh array code of the flags of a command.
 */
function zsh_command_flag_args($cmd) {
    $args = array();
    $specs = $cmd->getOptionSpecs();

    /*
    '(- 1 *)--version[display version and copyright information]' \
    '(- 1 *)--help[print a short help statement]' \
    */
    foreach ($specs->options as $opt ) {
        $args[] = zsh_option_flag_item($opt);
    }
    return $args;
}


class ZshCompletionCommand extends Command
    implements CommandInterface
{

    public function brief() { return 'This function generate a zsh-completion script automatically'; }

    public function execute() {
        global $argv;
        $programName = $argv[0];
        $compName = "_" . preg_replace('#\W+#','_',$programName);

        /* for debug
            $programName = 'foo';
            $compName = '_foo';
         */

        // var_dump( $argv ); 
        $code = "";
        $code .= "# THIS IS AN AUTO-GENERATED FILE, PLEASE DON'T MODIFY THIS FILE DIRECTLY.\n";


        $app = $this->getApplication();
        $cmds = $app->getCommandObjects();

        $zCmds  = zsh_command_desc_array($cmds);
        $zCmds .= "_describe -t commands 'command' commands && ret=0\n";
        $zCmds = indent_str($zCmds, 3);

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
{$zCmds}
    ;;
    (args)
      curcontext="\${curcontext%:*:*}:$programName-cmd-\$words[1]:"
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
            // XXX: support alias
            $_args = zsh_command_flag_args($cmd);
            $_code = '';
            $_code .= "(" . $k . ")\n";

            // TODO: get argument spec from command class -> execute method

            if ($_args) {
            $_code .= "  _arguments \\\n";
            $_code .= "    " . join(" \\\n",$_args) . "\\\n";
            $_code .= "    && ret=0\n";
            }

            $_code .= "   ;;\n";
            $code .= indent_str($_code,2);
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
