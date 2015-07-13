<?php
namespace CLIFramework\Completion;
use CLIFramework\Buffer;
use CLIFramework\Application;
use CLIFramework\ArgInfo;
use CLIFramework\CommandBase;
use CLIFramework\Command;
use CLIFramework\Completion\Utils;
use GetOptionKit\Option;
use Exception;

function indent($level) {
    return str_repeat('  ', $level);
}

function str_indent($content, $level = 1) {
    $space = str_repeat('  ', $level);
    $lines = explode("\n", $content);
    $lines = array_map(function($line) use ($space) { return $space . $line; }, $lines);
    return join("\n", $lines);
}

function array_indent(array $lines, $level = 1) {
    $space = str_repeat('  ', $level);
    return array_map(function($line) use ($space) {
        return $space . $line;
    }, $lines);
}

function join_indent(array $lines, $level = 1) {
    return join("\n",array_indent($lines, $level));
}

/**
 * wrap zsh code with function
 */
function zsh_comp_function($name, $code , $guard = false) {
    $buf = new Buffer;
    if ($guard) {
        $buf->appendLine("(( \$+functions[$name] )) ||");
    }
    $buf->appendLine("$name () {");
    $buf->indent();
    $buf->appendBuffer($code);
    $buf->unindent();
    $buf->appendLine("}");
    return $buf;
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

function zsh_comp_desc_array(array $array) {
    $out = new Buffer;
    $out->appendLine('(');
    $out->indent();
    foreach($array as $item => $desc) {
        $out->appendLine("'" . addslashes("$item:$desc") . "'");
    }
    $out->unindent();
    $out->appendLine(')');
    return $out;
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

    /**
     * @var string $bindName
     */
    public $bindName;

    public $buffer;

    public function __construct(Application $app, $programName, $bindName, $compName)
    {
        $this->app = $app;
        $this->programName = $programName;
        $this->compName = $compName;
        $this->bindName = $bindName;
        $this->buffer = new Buffer;
    }


    public function output() {
        return $this->complete_application();
    }

    public function visible_commands(array $cmds) {
        $visible = array();
        foreach ($cmds as $name => $cmd) {
            if (! preg_match('#^_#', $name) ) {
                $visible[$name] = $cmd;
            }
        }
        return $visible;
    }

    public function command_desc_array(array $cmds) {
        $args = array();
        foreach ( $cmds as $name => $cmd ) {
            if ( preg_match('#^_#', $name) ) {
                continue;
            }
            $args[] = "$name:" . Utils::q($cmd->brief());
        }
        return $args;
    }


    public function describe_commands(array $cmds, $level = 0) {
        $buf = new Buffer;
        $buf->setIndent($level);
        $buf->appendLine("local commands; commands=(");
        $buf->indent();
        $buf->appendLines($this->command_desc_array($cmds));
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
    public function option_flag_item(Option $opt, $cmdSignature) {
        // TODO: Check conflict options
        $str = "";

        $optspec = $opt->flag || $opt->optional ? '' : '=';
        $optName = $opt->long ? $opt->long : $opt->short;

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
        $str .= "[" . addcslashes($opt->desc,'[]:') . "]";

        $placeholder = ($opt->valueName) ? $opt->valueName : $opt->isa ? $opt->isa : null;

        // has anything to complete
        if ($opt->validValues || $opt->suggestions || $opt->isa) {

            $str .= ':'; // for the value name

            if ($placeholder) {
                $str .= $placeholder;
            }

            if ($opt->validValues || $opt->suggestions) {
                if ($opt->validValues) {
                    if ( is_callable($opt->validValues) ) {
                        $str .= ':{' . join(' ', array($this->meta_command_name(), Utils::qq($placeholder), $cmdSignature, 'opt', $optName, 'valid-values')) . '}';
                    } elseif ($values = $opt->getValidValues()) {
                        // not callable, generate static array
                        $str .= ':(' . join(' ', Utils::array_qq($values)) . ')';
                    }
                } elseif ($opt->suggestions) {
                    if ( is_callable($opt->suggestions) ) {
                        $str .= ':{' . join(' ', array($this->meta_command_name(), Utils::qq($placeholder), $cmdSignature, 'opt', $optName, 'suggestions') ) . '}';
                    } elseif ($values = $opt->getSuggestions()) {
                        // not callable, generate static array
                        $str .= ':(' . join(' ', Utils::array_qq($values)) . ')';
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
    public function command_args(CommandBase $cmd, $cmdSignature) {
        $args = array();
        $arginfos = $cmd->getArgInfoList();

        // for command that does not define an argument, we just complete the argument by file paths.
        if (empty($arginfos)) {
            return array("*:default:_files");
        }

        $idx = 0;
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
                    if (is_callable($a->validValues)) {
                        $comp .= ':{' . join(' ', array($this->meta_command_name(), Utils::qq($a->name), $cmdSignature, 'arg', $idx, 'valid-values')) . '}';
                    } elseif ($values = $a->getValidValues()) {
                        $comp .= ':(' . join(" ", Utils::array_qq($values)) . ')';
                    }
                } elseif ($a->suggestions ) {
                    if (is_callable($a->suggestions)) {
                        $comp .= ':{' . join(' ', array($this->meta_command_name(), Utils::qq($a->name), $cmdSignature, 'arg', $idx, 'suggestions')) . '}';
                    } elseif ($values = $a->getSuggestions()) {
                        $comp .= ':(' . join(" ", Utils::array_qq($values)) . ')';
                    }
                }
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
            $args[] = Utils::q($comp);
            $idx++;
        }
        return empty($args) ? NULL : $args;
    }


    /**
     * Complete commands with options and its arguments (without subcommands)
     */
    public function complete_command_options_arguments(CommandBase $subcmd, $level = 1) {
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

    public function render_argument_completion_handler(ArgInfo $a) {
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



    public function render_argument_completion_values(ArgInfo $a) {
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
    public function command_args_case(CommandBase $cmd) {
        $buf = new Buffer;
        $arginfos = $cmd->getArgInfoList();
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
    public function command_flags(CommandBase $cmd, $cmdSignature) {
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
    public function command_subcommand_states(CommandBase $cmd) {
        $args = array();
        $cmds = $this->visible_commands($cmd->getCommands());
        foreach($cmds as $c) {
            $args[] = sprintf("'%s:->%s'", $c->getName(), $c->getName()); // generate argument states
        }
        return $args;
    }

    public function commandmeta_function() {
        $buf = new Buffer;
        $buf->indent();
        $buf->appendLine("local curcontext=\$curcontext state line ret=1");
        $buf->appendLine("typeset -A opt_args");
        $buf->appendLine("typeset -A val_args");

        $buf->appendLine("declare -a lines");
        $buf->appendLine("declare -a args");

        $buf->appendLine("local ret=1");
        $buf->appendLine("local desc=\$1");
        $buf->appendLine("local cmdsig=\$2");
        $buf->appendLine("local valtype=\$3");
        $buf->appendLine("local pos=\$4");
        $buf->appendLine("local completion=\$5");

        $metaCommand = array($this->programName, 'meta', '--zsh', '$cmdsig', '$valtype', '$pos', '$completion');

        $buf->appendLine('output=$(' . join(" ",$metaCommand) . ')');

        // zsh: split lines into array
        // lines=("${(@f)output}") ; echo ${lines[1]}
        // ${(@f)$( )} expand lines to array
        $buf->appendLine('lines=("${(@f)output}")');
        $buf->appendLine('output_type=${lines[1]}');

        // TODO: support title
        $buf->appendLine('if [[ $lines[1] == "#groups" ]] ; then');

        $buf->appendLine('    eval $output');
        $buf->appendLine('    for tag in ${(k)groups} ; do');
        $buf->appendLine('        complete_values=(${(z)${groups[$tag]}})');
        $buf->appendLine('        label=${labels[$tag]}');
        $buf->appendLine('        if [[ -z $label ]] ; then');
        $buf->appendLine('            label=$tag');
        $buf->appendLine('        fi');
        $buf->appendLine('        _describe -t $tag $label complete_values && ret=0');
        $buf->appendLine('    done');

        $buf->appendLine('elif [[ $lines[1] == "#values" ]] ; then');
        $buf->appendLine('    args=(${lines:1})');
        $buf->appendLine('   _values "$desc" ${=args} && ret=0');
        $buf->appendLine('elif [[ $lines[1] == "#descriptions" ]] ; then');
        $buf->appendLine('    args=(${lines:1})');
        $buf->appendLine('    _describe "$desc" args && ret=0');
        $buf->appendLine('else');
        $buf->appendLine('   _values "$desc" ${=lines} && ret=0');
        $buf->appendLine('fi');
        // $buf->appendLine('_values $desc ${=values} && ret=0'); // expand value array as arguments
        $buf->appendLine('return ret');
        return zsh_comp_function($this->meta_command_name(), $buf);
    }

    public function meta_command_name() {
        return '__' . preg_replace('/\W/','_',$this->compName) . 'meta';
    }


    /**
     * Zsh function usage
     *
     * example/demo meta commit arg 1 valid-values
     * appName meta sub1.sub2.sub3 opt email valid-values
     */
    public function commandmeta_callback_function(CommandBase $cmd) {
        $cmdSignature = $cmd->getSignature();
        
        
        $buf = new Buffer;
        $buf->indent();

        $buf->appendLine("local curcontext=\$curcontext state line ret=1");
        $buf->appendLine("declare -A opt_args");
        $buf->appendLine("declare -A values");
        $buf->appendLine("local ret=1");

        /*
        values=$(example/demo meta commit arg 1 valid-values)
        _values "description" ${=values} && ret=0
        return ret
        */
        $buf->appendLine("local desc=\$1");
        $buf->appendLine("local valtype=\$3");
        $buf->appendLine("local pos=\$4");
        $buf->appendLine("local completion=\$5");

        $metaCommand = array($this->programName,'meta', $cmdSignature, '$valtype', '$pos', '$completion');
        $buf->appendLine('$(' . join(" ",$metaCommand) . ')');
        $buf->appendLine('_values $desc ${=values} && ret=0'); // expand value array as arguments
        $buf->appendLine('return ret');

        $funcName = $this->command_function_name($cmdSignature);
        return zsh_comp_function($funcName, $buf);
    }

    public function commandmeta_callback_functions(CommandBase $cmd) {
        $cmdSignature = $cmd->getSignature();
        


        $buf = new Buffer;
        $subcmds = $this->visible_commands($cmd->getCommands());
        foreach($subcmds as $subcmd) {
            $buf->append($this->commandmeta_callback_function($subcmd));

            if ($subcmd->hasCommands()) {
                $buf->appendBuffer( $this->commandmeta_callback_functions($subcmd) );
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

        $metaName = '_' . $this->programName . 'meta';

        $buf->append( $this->commandmeta_function() );

        $buf->appendLines(array(
            "{$this->compName}() {",
            "local curcontext=\$curcontext state line",
            "typeset -A opt_args",
            "local ret=1",
            $this->complete_with_subcommands($this->app), // create an empty command name stack and 1 level indent
            "return ret",
            "}",
            "compdef {$this->compName} {$this->bindName}"
        ));
        return $buf->__toString();
    }


    /**
     * Convert command signature "foo.bar-top" into zsh function name "__foo_bar_top"
     *
     * @param string $sig
     */
    public function command_function_name($signature) {
        return '__' . $this->compName . '_' . preg_replace('#\W#','_', $signature);
    }


    public function complete_with_subcommands(CommandBase $cmd, $level = 1) {
        $cmdSignature = $cmd->getSignature();

        $buf = new Buffer;
        $buf->setIndent($level);

        $subcmds = $this->visible_commands($cmd->getCommands());
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
                $buf->appendBlock($this->complete_with_subcommands($subcmd, $level + 1));
            } else {
                $buf->appendBlock($this->complete_command_options_arguments($subcmd, $level + 1));
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


