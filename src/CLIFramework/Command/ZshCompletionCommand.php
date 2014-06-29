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
 * Return the zsh array code of the flags of a command.
 */
function zsh_command_flag_array($cmd) {
    return $cmd->getOptionSpecs();
}


class ZshCompletionCommand extends Command
    implements CommandInterface
{

    public function brief() { return 'This function generate a zsh-completion script automatically'; }

    public function execute() {
        global $argv;
        $programName = $argv[0];
        $compName = "_" . preg_replace('#\W+#','_',$programName);

        // var_dump( $argv ); 
        $code = "";
        $code .= "# THIS IS AN AUTO-GENERATED FILE, PLEASE DON'T MODIFY THIS FILE DIRECTLY.\n";


        $app = $this->getApplication();
        $cmds = $app->getCommandObjects();

        $zCmds  = zsh_command_desc_array($cmds);
        $zCmds .= "_describe -t commands 'command' commands && ret=0\n";
        $zCmds = indent_str($zCmds, 3);


        foreach ($cmds as $cmd) {
            $specs = $cmd->getOptionSpecs();
            foreach( $specs->longOptions as $opt ) {
                $opt->isAttributeFlag();
                $opt->isAttributeRequire();
                $opt->isAttributeOptional();
                var_dump( $opt->short, $opt->long, $opt->description );
            }
            foreach( $specs->shortOptions as $opt ) {
                $opt->isAttributeFlag();
                $opt->isAttributeRequire();
                $opt->isAttributeOptional();
                var_dump( $opt->short, $opt->long, $opt->description );
            }
        }

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
  esac
  return ret
}

compdef $compName $programName

HEREDOC;




        echo $code;
    }




}
