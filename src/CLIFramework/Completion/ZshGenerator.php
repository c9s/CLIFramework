<?php
namespace CLIFramework\Completion;
use CLIFramework\Buffer;
use Exception;
use CLIFramework\Application;

function indent($level) {
    return str_repeat('  ', $level);
}

function quote($str) {
    return '"' . addcslashes($str , '"') . '"';
}

function single_quote($str) {
    return "'" . addslashes($str) . "'";
}

function array_quote($array) {
    return array_map("quote", $array);
}

function array_single_quote($array) {
    return array_map("single_quote", $array);
}

function str_indent($content, $level = 1) {
    $space = str_repeat('  ', $level);
    $lines = explode("\n", $content);
    $lines = array_map(function($line) use ($space) { return $space . $line; }, $lines);
    return join("\n", $lines);
}

function array_indent($lines, $level = 1) {
    $space = str_repeat('  ', $level);
    return array_map(function($line) use ($space) {
        return $space . $line;
    }, $lines);
}

function join_indent($lines, $level = 1) {
    return join("\n",array_indent($lines, $level));
}

/**
 * wrap zsh code with function
 */
function zsh_comp_function($name, $code) {
    return "(( \$+functions[$name] )) ||\n"
        . "$name () {\n"
        . str_indent($code, 1) . "\n"
        . "}\n"
        ;
}

function case_in($name, $code) {
    return "case $name in\n"
        . str_indent($code, 1) . "\n"
        . "esac\n"
        ;
}


function case_case($pattern, $code) {
    return "($pattern)\n"
        . str_indent($code, 1) . "\n"
        . ";;\n"
        ;
}


class ZshGenerator
{
    public $app;

    /**
     * @var string $program
     */
    public $programName;

    /**
     * @var string $compName
     */
    public $compName;

    public $buffer;

    public function __construct($app, $programName, $compName)
    {
        $this->app = $app;
        $this->programName = $programName;
        $this->compName = $compName;
        $this->buffer = new Buffer;
    }


    public function output() {
        return $this->complete_application();
    }

    public function command_desc_item($name, $cmd) {
        return "'$name:" . addslashes($cmd->brief()) . "'";
    }

    public function visible_commands($cmds) {
        $visible = array();
        foreach ( $cmds as $name => $cmd ) {
            if ( ! preg_match('#^_#', $name) ) {
                $visible[$name] = $cmd;
            }
        }
        return $visible;
    }

    public function command_desc_array($cmds) {
        $args = array();
        foreach ( $cmds as $name => $cmd ) {
            if ( preg_match('#^_#', $name) ) {
                continue;
            }
            $args[] = $this->command_desc_item($name, $cmd);
        }
        return $args;
    }


    public function describe_commands($cmds, $level = 0) {
        $buf = new Buffer;
        $buf->setIndent($level);
        $array  = $this->command_desc_array($cmds);
        $buf->appendLine("local commands; commands=(");
        $buf->indent();
        $buf->appendLines($array);
        $buf->unindent();
        $buf->appendLine(")");
        $buf->appendLine("_describe -t commands 'command' commands && ret=0");
        return $buf;
    }



    /**
    *
    *
    * Generate an zsh option format like this:
   
    '(-v --invert-match)'{-v,--invert-match}'[invert match: select non-matching lines]'
    
    Or:
    
    '-gcflags[flags for 5g/6g/8g]:flags'
    '-p[number of parallel builds]:number'

    '--cleanup=[specify how the commit message should be cleaned up]:mode:((verbatim\:"do not change the commit message at all"
                                                                            whitespace\:"remove leading and trailing whitespace lines"
                                                                            strip\:"remove both whitespace and commentary lines"
                                                                            default\:"act as '\''strip'\'' if the message is to be edited and as '\''whitespace'\'' otherwise"))' \
    */
    public function option_flag_item($opt, $cmdSignature) {
        // TODO: Check conflict options
        $str = "";

        $optspec = $opt->flag || $opt->optional ? '' : '=';

        if ($opt->short && $opt->long) {
            if (!$opt->multiple) {
                $str .= "'(-" . $opt->short . " --" . $opt->long . ")'"; // conflict options
            }
            $str .= "{-" . $opt->short . ',' . '--' . $opt->long . $optspec . "}";
            $str .= "'";
        } else if ($opt->long) {
            $str .= "'--" . $opt->long . $optspec;
        } else if ($opt->short) {
            $str .= "'-" . $opt->short . $optspec;
        } else {
            throw new Exception('undefined option type');
        }

        // output description
        $str .= "[" . addslashes($opt->desc) . "]";


        // has anything to complete
        if ($opt->validValues || $opt->suggestions || $opt->isa) {

            $str .= ':'; // for the value name
            if ($opt->valueName) {
                $str .= $opt->valueName;
            } elseif ($opt->isa) {
                $str .= $opt->isa;
            }

            if ($opt->validValues || $opt->suggestions) {
                $values = array();

                if ($opt->validValues) {
                    if ( is_callable($opt->suggestions) ) {
                        // XXX:
                        // $str .= ':';
                    } elseif ($values = $opt->getValidValues()) {
                        // not callable, generate static array
                        $str .= ':(' . join(' ', $values) . ')';
                    }
                } elseif ($opt->suggestions) {
                    if ( is_callable($opt->suggestions) ) {
                        // XXX:
                    } elseif ($values = $opt->getSuggestions()) {
                        // not callable, generate static array
                        $str .= ':(' . join(' ', $values) . ')';
                    }
                }

            } elseif ( in_array($opt->isa, array('file', 'dir', 'path')) ) {
                switch($opt->isa) {
                    case 'file':
                        $str .= ':_files';
                    break;
                    case 'dir':
                        $str .= ':_directories';
                    break;
                    case 'path':
                        $str .= ':_path_files';
                    break;
                }
                if ( isset($opt->glob) ) {
                    $str .= ' -g "' . $opt->glob . '"';
                }
            }
        }


        $str .= "'"; // close quote
        return $str;
    }


    /**
    * Return args as a alternative
    *
    *  "*:args:{ _alternative ':importpaths:__go_list' ':files:_path_files -g \"*.go\"' }"
    */
    public function command_args($cmd, $cmdSignature) {
        $args = array();
        $arginfos = $cmd->getArgumentsInfo();

        $idx = 1;
        foreach($arginfos as $a) {
            $comp = '';

            if ($a->multiple) {
                $comp .= '*:' . $a->name;
            } else {
                $comp .= ':' . $a->name;
            }

            if ($a->validValues || $a->suggestions) {
                $values = array();
                if ($a->validValues) {
                    $values = $a->getValidValues();
                } elseif ($a->suggestions ) {
                    $values = $a->getSuggestions();
                }
                $comp .= ':(' . join(" ", $values) . ')';
            } elseif (in_array($a->isa,array('file','path','dir'))) {
                switch($a->isa) {
                    case "file":
                        $comp .= ":_files";
                        break;
                    case "path":
                        $comp .= ":_path_files";
                        break;
                    case "dir":
                        $comp .= ":_directories";
                        break;
                }
                if ($a->glob) {
                    $comp .= " -g \"{$a->glob}\"";
                }
            }
            $args[] = single_quote($comp);
        }
        return empty($args) ? NULL : $args;
    }


    /**
     * Complete commands with options and its arguments (without subcommands)
     */
    public function complete_command_options_arguments($subcmd, $level = 1, $cmdNameStack = array() ) {
        if (!$subcmd instanceof Application) {
            $cmdNameStack[] = $subcmd->getName();
        }
        // $cmdSignature = $this->command_signature($cmdNameStack);
        $cmdSignature = $subcmd->getSignature();


        $buf = new Buffer;
        $buf->setIndent($level);

        $args  = $this->command_args($subcmd, $cmdSignature);
        $flags = $this->command_flags($subcmd, $cmdSignature);

        if ( $flags || $args ) {
            $buf->appendLine("_arguments -w -S -s \\");
            $buf->indent();

            if ($flags) {
                foreach($flags as $line) {
                    $buf->appendLine($line . " \\");
                }
            }
            if ($args) {
                foreach($args as $line) {
                    $buf->appendLine($line . " \\");
                }
            }
            $buf->appendLine(" && ret=0");
            $buf->unindent();
        }
        return $buf->__toString();
    }

    public function render_argument_completion_handler($a) {
        $comp = '';
        switch($a->isa) {
            case "file":
                $comp .= "_files";
                break;
            case "path":
                $comp .= "_path_files";
                break;
            case "dir":
                $comp .= "_directories";
                break;
        }
        if ($a->glob) {
            $comp .= " -g \"{$a->glob}\"";
        }
        return $comp;
    }



    public function render_argument_completion_values($a) {
        if ($a->validValues || $a->suggestions) {
            $values = array();
            if ($a->validValues) {
                $values = $a->getValidValues();
            } elseif ($a->suggestions ) {
                $values = $a->getSuggestions();
            }
            return join(" ", $values);
        }
        return '';
    }




    /**
     * complete argument cases
     */
    public function command_args_case($cmd) {
        $buf = new Buffer;
        $arginfos = $cmd->getArgumentsInfo();
        $idx = 1;

        $buf->appendLine("case \$state in");

        foreach($arginfos as $a) {
            $buf->appendLine("({$a->name})");

            if ($a->validValues || $a->suggestions) {
                $buf->appendLine("_values " . $this->render_argument_completion_values($a) . ' && ret=0');
            } elseif (in_array($a->isa,array('file','path','dir'))) {
                $buf->appendLine($this->render_argument_completion_handler($a) . ' && ret=0');
            }
            $buf->appendLine(";;");
        }
        $buf->appendLine("esac");
        return $buf->__toString();
    }

    /**
     * Return the zsh array code of the flags of a command.
     *
     * @return string[]
     */
    public function command_flags($cmd, $cmdSignature) {
        $args = array();
        $specs = $cmd->getOptionCollection();
        /*
        '(- 1 *)--version[display version and copyright information]' \
        '(- 1 *)--help[print a short help statement]' \
        */
        foreach ($specs->options as $opt ) {
            $args[] = $this->option_flag_item($opt, $cmdSignature);
        }
        return empty($args) ? NULL : $args;
    }


    /**
     * Return subcommand completion status as an array of string
     *
     * @param Command $cmd The command object
     *
     * @return string[]
     */
    public function command_subcommand_states($cmd) {
        $args = array();
        $cmds = $this->visible_commands($cmd->getCommandObjects());
        foreach($cmds as $c) {
            $args[] = sprintf("'%s:->%s'", $c->getName(), $c->getName(), $c->getName()); // generate argument states
        }
        return $args;
    }


    /**
     * Zsh function usage
     *
     * example/demo _meta commit arg 1 valid-values
     * appName _meta sub1.sub2.sub3 opt email valid-values
     *
     * TODO: Use _values to provide completion
     */
    public function command_meta_callback_function($cmd, $prefix = null, $cmdNameStack = array()) {
        if (! $cmd instanceof Application) {
            $cmdNameStack[] = $cmd->getName();
        }
        $cmdSignature = $cmd->getSignature();
        
        
        $buf = new Buffer;
        $buf->indent();

        $buf->appendLine("local curcontext=\$curcontext state line ret=1");
        $buf->appendLine("declare -A opt_args");
        $buf->appendLine("local ret=1");

        /*
        local curcontext=$curcontext state line ret=1
        declare -A opt_args
        local ret=1
        declare -a values
        values=$(example/demo _meta commit arg 1 valid-values)

        # =values to expand values
        _values "description" ${=values} && ret=0
        return ret
         */


        $flags = $this->command_flags($cmd, $cmdSignature);
        $args  = $this->command_args($cmd, $cmdSignature);

        if ($flags || $args) {
            $buf->appendLine("_arguments -w -C -S -s \\");
            $buf->indent();
            if ($flags) {
                foreach( $flags as $flag ) {
                    $buf->appendLine($flag . " \\");
                }
            }

            if ($args) {
                foreach( $args as $arg ) {
                    $buf->appendLine($arg . " \\");
                }
            }
            $buf->appendLine("&& ret=0");
            $buf->unindent();
            $buf->appendBlock($this->command_args_case($cmd));
        }
        $buf->appendLine("return ret");

        $funcName = preg_replace('#\W#','_',join('_', $cmdNameStack));

        if ($prefix) {
            $funcName = $prefix . $funcName;
        }
        return zsh_comp_function($funcName, $buf->__toString());
    }

    public function command_meta_callback_functions($programName, $cmd, $cmdNameStack = array() ) {
        if (! $cmd instanceof Application) {
            $cmdNameStack[] = $cmd->getName();
        }
        $cmdSignature = $cmd->getSignature();
        


        $buf = new Buffer;
        $subcmds = $this->visible_commands($cmd->getCommandObjects());
        foreach($subcmds as $subcmd) {
            $buf->append($this->command_meta_callback_function($subcmd, '__', $cmdNameStack));

            if ($subcmd->hasCommands()) {
                $buf->appendBuffer( $this->command_meta_callback_functions($programName, $subcmd, $cmdNameStack) );
            }
        }
        return $buf;
    }

    public function complete_application() {
        $buf = new Buffer;
        $buf->appendLines(array(
            "# {$this->programName} zsh completion script generated by CLIFramework",
            "# Web: http://github.com/c9s/php-CLIFramework",
            "# THIS IS AN AUTO-GENERATED FILE, PLEASE DON'T MODIFY THIS FILE DIRECTLY.",
        ));

        $metaName = '_' . $this->programName . '_meta';

        $buf->appendLines(array(
'(( $+functions['.$metaName.'] )) ||',
$metaName . ' () {',
    'local curcontext=$curcontext state line ret=1',
    'declare -A opt_args',
    'local ret=1',
    'declare -a values',
    'values=$(example/demo _meta $2 $3 $4 $5)',
    '# =values to expand values',
    '_values $1 ${=values} && ret=0',
    'return ret',
'}',
        ));


        $buf->appendBuffer( $this->command_meta_callback_functions($this->programName, $this->app) );

        $buf->appendLines(array(
            "{$this->compName}() {",
            "local curcontext=\$curcontext state line",
            "typeset -A opt_args",
            "local ret=1",
            $this->complete_with_subcommands($this->programName, $this->app), // create an empty command name stack and 1 level indent
            "return ret",
            "}",
            "compdef {$this->compName} {$this->programName}"
        ));
        return $buf->__toString();
    }


    public function command_signature($cmdNameStack = array()) {
        if (!empty($cmdNameStack)) {
            return preg_replace('#[^.]#','_', join('.', $cmdNameStack));
        }
        return '';
    }


    public function complete_with_subcommands($programName, $cmd, $level = 1, $cmdNameStack = array() ) {
        if (! $cmd instanceof Application) {
            $cmdNameStack[] = $cmd->getName();
        }
        $cmdSignature = $this->command_signature($cmdNameStack);

        $buf = new Buffer;
        $buf->setIndent($level);

        $subcmds = $this->visible_commands($cmd->getCommandObjects());
        $descsBuf  = $this->describe_commands($subcmds, $level);

        $code = array();

        // $code[] = 'echo $words[$CURRENT-1]';

        $buf->appendLine("_arguments -C \\");
        $buf->indent();

        if ($args = $this->command_flags($cmd, $cmdSignature)) {
            foreach ($args as $arg) {
                $buf->appendLine($arg . " \\");
            }
        }
        $buf->appendLine("': :->cmds' \\");
        $buf->appendLine("'*:: :->option-or-argument' \\");
        $buf->appendLine(" && return");
        $buf->unindent();

        $buf->appendLine("case \$state in");
        $buf->indent();

        $buf->appendLine("(cmds)");
        $buf->appendBuffer($descsBuf);
        $buf->appendLine(";;");

        $buf->appendLine("(option-or-argument)");

        // $code[] = "  curcontext=\${curcontext%:*:*}:$programName-\$words[1]:";
        // $code[] = "  case \$words[1] in";

        $buf->indent();
        $buf->appendLine("curcontext=\${curcontext%:*}-\$line[1]:");
        $buf->appendLine("case \$line[1] in");
        $buf->indent();
        foreach ($subcmds as $k => $subcmd) {
            // TODO: support alias
            $buf->appendLine("($k)");

            if ($subcmd->hasCommands()) {
                $buf->appendBlock($this->complete_with_subcommands($programName, $subcmd, $level + 1, $cmdNameStack));
            } else {
                $buf->appendBlock($this->complete_command_options_arguments($subcmd, $level + 1, $cmdNameStack));
            }
            $buf->appendLine(";;");
        }
        $buf->unindent();
        $buf->appendLine("esac");
        $buf->appendLine(";;");
        $buf->unindent();
        $buf->appendLine("esac");
        return $buf->__toString();
    }
}


