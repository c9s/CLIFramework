<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use CLIFramework\CommandInterface;
use CLIFramework\Zsh;
use Exception;

function output($str) {
    echo $str;
}

function output_values($values) {
    echo join("\n", $values);
}

class MetaCommand extends Command
{

    public function brief() { return 'meta command of cli framework'; }
    
    /**
     * Enable a way to get meta information of argument or option from a command.
     *
     *     app _meta sub1.sub2.sub3 arg 1 valid-values
     *     app _meta sub1.sub2.sub3 arg 1 suggestions
     *     app _meta sub1.sub2.sub3 opt email valid-values
     */
    public function execute($commandlist, $type, $arg, $attr) {
        $commands = explode('.', $commandlist);
        // lookup commands
        $app = $this->getApplication();

        $cmd = $app;
        while ($cmd->hasCommands()) {
            $cmd = $cmd->getCommand( array_pop($commands) );
        }

        if ( !$cmd) {
            throw new Exception("Can not find command.");
        }

        switch($type) {
        case 'arg':
            $idx = intval($arg);
            $arginfos = $cmd->getArgumentsInfo();

            if ( ! isset($arginfos[ $idx ]) ) {
                throw new Exception("Undefined argument at $idx");
            }

            $arginfo = $arginfos[ $idx ];
            switch($attr) {
            case 'suggestions':
                if ($values = $arginfo->getSuggestions()) {
                    return output_values($values);
                }
                break;

            case 'valid-values':
                if ($values = $arginfo->getValidValues()) {
                    return output_values($values);
                }
                break;
            }
            break;
        case 'opt':
            $options = $cmd->getOptionCollection();
            $option = $options->find($arg);
            if (!$option) {
                throw new Exception("Option $arg not found");
            }
            switch ($attr) {
            case 'isa':
                return output($option->isa);
                break;
            case 'valid-values':
                if ($values = $option->getValidValues()) {
                    return output_values($values);
                }
                break;
            case 'suggestions':
                if ($values = $option->getSuggestions()) {
                    return output_values($values);
                }
                break;
            }
            break;
        default:
            echo "unsupported type\n";
            break;
        }

        // find argument or find option

        // return the information

    }
}

    
