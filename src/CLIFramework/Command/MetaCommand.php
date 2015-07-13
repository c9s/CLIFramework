<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use CLIFramework\CommandInterface;
use CLIFramework\Zsh;
use CLIFramework\ValueCollection;
use CLIFramework\Buffer;
use CLIFramework\CommandBase;
use GetOptionKit\OptionCollection;
use GetOptionKit\OptionResult;
use Exception;
use InvalidArgumentException;

class UnsupportedShellException extends Exception { }

class UndefinedArgumentException extends Exception {}

class UndefinedOptionException extends Exception 
{
    public $options;
    public $command;

    public function __construct($message, CommandBase $command, OptionCollection $options)
    {
        $this->command = $command;
        $this->options = $options;
        parent::__construct($message);
    }

}

function output($str, OptionResult $opts = NULL) {
    echo $str;
}

function array_escape_space(array $array) {
    return array_map(function($c) {
        return addcslashes($c, ' ');
    }, $array);
}

function is_indexed_array(array $array) {
    $keys = array_keys($array);
    $numericKey = true;
    foreach($keys as $key) {
        if (!is_numeric($key)) {
            $numericKey = false;
        }
    }
    return $numericKey;
}

function is_assoc_array(array $array) {
    if (empty($array)) {
        return false;
    }
    $keys = array_keys($array);
    $numericKey = true;
    foreach($keys as $key) {
        if (!is_numeric($key)) {
            $numericKey = false;
        }
    }
    return !$numericKey;
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

    public function brief() { return 'Return the meta data of a commands.'; }

    public function options($opts) {
        $opts->add('flat', 'flat list format. work for both zsh and bash.');
        $opts->add('zsh', 'output for zsh');
        $opts->add('bash', 'output for bash');
        $opts->add('json', 'output in JSON format (un-implemented)');
    }
    
    /**
     * Enable a way to get meta information of argument or option from a command.
     *
     *     app meta sub1.sub2.sub3 arg 1 valid-values
     *     app meta sub1.sub2.sub3 arg 1 suggestions
     *     app meta sub1.sub2.sub3 opt email valid-values
     */
    public function execute($commandlist, $type, $arg = NULL, $attr = NULL) {
        $commandNames = explode('.', $commandlist);
        // lookup commands
        $app = $this->getApplication();

        $cmd = $app;

        if ($commandNames[0] === "app") {
            array_shift($commandNames);
        }

        $this->logger->debug("Finding command " . get_class($cmd));

        while (!empty($commandNames) && $cmd->hasCommands()) {
            $commandName = array_shift($commandNames);
            $this->logger->debug("Finding command " . $commandName);
            $cmd = $cmd->getCommand($commandName);
            $this->logger->debug("Found command class " . get_class($cmd));
        }

        // 'arg' or 'opt' require the argument name and attribute type
        if (in_array($type, array('arg', 'opt')) && $arg === NULL || $attr === NULL) {
            throw new InvalidArgumentException("'arg' or 'opt' require the attribute type.");
        }


        try {

            if ( !$cmd) {
                throw new Exception("Can not find command.");
            }

            switch($type) {
            case 'arg':
                $idx = intval($arg);
                $arginfos = $cmd->getArgInfoList();

                if (! isset($arginfos[ $idx ]) ) {
                    throw new UndefinedArgumentException("Undefined argument at $idx");
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
            case 'opts':
                $options = $cmd->getOptionCollection();
                $values = array();
                foreach ($options as $opt) {
                    if ($opt->short) {
                        $values[] = "-" . $opt->short;
                    } elseif ($opt->long) {
                        $values[] = "--" . $opt->long;
                    }
                }
                echo join(' ', $values) , "\n";
                return ;
                break;
            case 'opt':
                $options = $cmd->getOptionCollection();
                $option = $options->find($arg);
                if (!$option) {
                    throw new UndefinedOptionException("Option '$arg' not found", $cmd, $options);
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
                throw new Exception("Invalid type '$type', valid types are 'arg', 'opt', 'opts'");
                break;
            }
        } catch (UnsupportedShellException $e) {

            fwrite(STDERR, $e->getMessage() . "\n");
            fwrite(STDERR, "Supported shells: zsh, bash\n");

        } catch (UndefinedOptionException $e) {
            fwrite(STDERR, $e->command->getSignature() . "\n");
            fwrite(STDERR, $e->getMessage() . "\n");
            fwrite(STDERR, "Valid options:\n");
            foreach ($e->options as $opt) {
                if ($opt->short && $opt->long) {
                    fwrite(STDERR, " " . $opt->short . '|' . $opt->long);
                } elseif ($opt->short) {
                    fwrite(STDERR,  " " . $opt->short);
                } elseif ($opt->long) {
                    fwrite(STDERR,  " " . $opt->long);
                }
                fwrite(STDERR, "\n");
            }
        }
    }

    public function outputValues($values, OptionResult $opts) {
        // indexed array
        if (is_array($values) && empty($values)) {
            return;
        }

        // encode complex data structure to shell
        if ($values instanceof ValueCollection) {

            // this output format works both in zsh & bash
            if ($opts->flat) {
                $buf = new Buffer;
                $buf->appendLine("#flat");
                foreach( $values as $groupId => $groupValues) {
                    foreach($groupValues as $val) {
                        $buf->appendLine($val);
                    }
                }
                $this->logger->write($buf);
            } elseif ($opts->zsh || $opts->bash) {
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
                throw new UnsupportedShellException();
            }
            return;
        }

        // for assoc array in indexed array
        if (is_array($values) && is_indexed_array($values) && is_array(end($values))) {
            $this->logger->writeln("#descriptions");
            if ($opts->zsh) {
                // for zsh, we output the first line as the label
                foreach($values as $value) {
                    list($key,$val) = $value;
                    $this->logger->writeln("$key:" . addcslashes($val,":"));
                }
            } else {
                foreach($values as $value) {
                    $this->logger->writeln($value[0]);
                }
            }

        } elseif (is_array($values) && is_indexed_array($values)) { // indexed array is a list.
            $this->logger->writeln("#values");
            $this->logger->writeln(join("\n", $values));
        } else { // associative array
            $this->logger->writeln("#descriptions");
            if ($opts->zsh) {
                foreach($values as $key => $desc) {
                    $this->logger->writeln("$key:" . addcslashes($desc,":"));
                }
            } else {
                foreach($values as $key => $desc) {
                    $this->logger->writeln($key);
                }
            }
        }
    }
}

    
