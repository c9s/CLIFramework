<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use CLIFramework\CommandInterface;
use CLIFramework\Zsh;
use CLIFramework\ValueCollection;
use Exception;
use CLIFramework\Buffer;

function output($str, $opts) {
    echo $str;
}


function is_assoc_array($array) {
    if (empty($array)) {
        return false;
    }
    $keys = array_keys($array);
    return ! is_integer($keys[0]);
}

function as_shell_string($str) {
    return '"' . addcslashes($str, '"') . '"';
}

/**
 * currenty it supports zsh format encode "label:desc"
 */
function encode_array_as_shell_string($array) {
    if (is_assoc_array($array)) {
        $output = array();
        foreach($array as $key => $val) {
            $output[] = $key . ":" . addcslashes($val,": ");
        }
        return '"' . addcslashes(join(" ",$output), '"') . '"';
    } else {
        $output = array();
        foreach($array as $val) {
            $output[] = addcslashes($val,": ");
        }
        return '"' . addcslashes(join(" ",$output), '"') . '"';
    }
}


class MetaCommand extends Command
{

    public function brief() { return 'meta command of cli framework'; }

    public function options($opts) {
        $opts->add('zsh', 'output for zsh');
        $opts->add('bash', 'output for bash');
        $opts->add('json', 'output in JSON format (un-implemented)');
    }
    
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

            $argInfo = $arginfos[$idx];

            switch($attr) {
            case 'suggestions':
                if ($values = $argInfo->getSuggestions()) {
                    return $this->outputValues($values, $this->options);
                }
                break;

            case 'valid-values':
                if ($values = $argInfo->getValidValues()) {
                    return $this->outputValues($values, $this->options);
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
                    return $this->outputValues($values, $this->options);
                }
                break;
            case 'suggestions':
                if ($values = $option->getSuggestions()) {
                    return $this->outputValues($values, $this->options);
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

    public function outputValues($values, $opts) {
        // indexed array
        if (is_array($values) && empty($values)) {
            return;
        }

        // encode complex data structure to shell
        if ($values instanceof ValueCollection) {

            // this output format works both in zsh & bash
            if ($opts->zsh || $opts->bash) {
                $buf = new Buffer;
                $buf->appendLine("#groups");
                $buf->appendLine("declare -A groups");
                $buf->appendLine("declare -A labels");

                // zsh and bash only supports one dimensional array, so we can only output values in string and separate these values with space.
                foreach( $values as $groupId => $groupValues) {
                    $buf->appendLine("groups[$groupId]=" . encode_array_as_shell_string($groupValues));
                }
                foreach( $values->getGroupLabels() as $groupId => $label) {
                    $buf->appendLine("labels[$groupId]=" . as_shell_string($label));
                }
                $this->logger->write($buf);
            } elseif ($opts->json) {
                $this->logger->write($values->toJson());
            } else {
                throw new Exception('Unsupported shell');
            }
            return;
        }

        if (isset($values[0]) && is_array($values[0])) {
            $this->logger->writeln("#descriptions");
            foreach($values as $value) {
                list($key,$val) = $value;
                $this->logger->writeln("$key:" . addcslashes($val,":"));
            }
        } elseif (isset($values[0])) {
            $this->logger->writeln("#values");
            $this->logger->writeln(join("\n", $values));
        } else {
            $this->logger->writeln("#descriptions");
            foreach($values as $key => $val) {
                $this->logger->writeln("$key:" . addcslashes($val,":"));
            }
        }
    }
}

    
