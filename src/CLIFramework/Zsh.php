<?php
namespace CLIFramework;
use CLIFramework\Buffer;
use Exception;

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

function join_indent_continued($lines, $level = 1) {
    return join("\\\n", array_indent($lines, $level));
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


class Zsh
{
    public static function command_desc_item($name, $cmd) {
        return "'$name:" . addslashes($cmd->brief()) . "'";
    }

    public static function visible_commands($cmds) {
        $visible = array();
        foreach ( $cmds as $name => $cmd ) {
            if ( ! preg_match('#^_#', $name) ) {
                $visible[$name] = $cmd;
            }
        }
        return $visible;
    }

    public static function command_desc_array($cmds) {
        $args = array();
        foreach ( $cmds as $name => $cmd ) {
            if ( preg_match('#^_#', $name) ) {
                continue;
            }
            $args[] = self::command_desc_item($name, $cmd);
        }
        return $args;
    }


    public static function describe_commands($cmds) {
        $array  = Zsh::command_desc_array($cmds);
        $code  = "local commands; commands=(\n";
        $code .= join("\n", array_indent($array, 1) );
        $code .= ")\n";
        $code .= "_describe -t commands 'command' commands && ret=0\n";
        return $code;
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
    public static function option_flag_item($opt) {
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
                    $values = $opt->getValidValues();
                } elseif ($opt->suggestions) {
                    $values = $opt->getSuggestions();
                }
                if ($values) {
                    $str .= ':(' . join(' ', $values) . ')';
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
    public static function command_args($cmd) {
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
    public static function complete_command_options_arguments($subcmd, $level = 1) {
        $code = array();
        $args  = self::command_args($subcmd);
        $flags = self::command_flags($subcmd);

        if ( $flags || $args ) {
            $code[] = indent($level) . "_arguments -w -S -s \\";
            if ($flags) {
                $code[] = join_indent_continued($flags, $level + 1) . " \\";
            }
            if ($args) {
                $code[] = join_indent_continued($args, $level + 1) . " \\";
            }
            $code[] = indent($level + 1) . " && ret=0";
        }
        return join_indent($code, $level);
    }

    public static function render_argument_completion_handler($a) {
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



    public static function render_argument_completion_values($a) {
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
    public static function command_args_case($cmd) {
        $code = array();
        $arginfos = $cmd->getArgumentsInfo();
        $idx = 1;

        $code[] = "case \$state in";
        foreach($arginfos as $a) {
            $code[] = "  (" . $a->name . ")";
            if ($a->validValues || $a->suggestions) {
                $code[] = "     _values " . self::render_argument_completion_values($a) . ' && ret=0';
            } elseif (in_array($a->isa,array('file','path','dir'))) {
                $code[] = self::render_argument_completion_handler($a) . ' && ret=0';
            }
            $code[] = "  ;;";
        }
        $code[] = "esac";
        return $code;
    }

    /**
    * Return the zsh array code of the flags of a command.
    */
    public static function command_flags($cmd) {
        $args = array();
        $specs = $cmd->getOptionCollection();
        /*
        '(- 1 *)--version[display version and copyright information]' \
        '(- 1 *)--help[print a short help statement]' \
        */
        foreach ($specs->options as $opt ) {
            $args[] = self::option_flag_item($opt);
        }
        return empty($args) ? NULL : $args;
    }

    public static function command_subcommand_states($cmd) {
        $args = array();
        $cmds = self::visible_commands($cmd->getCommandObjects());
        foreach($cmds as $c) {
            $args[] = sprintf("'%s:->%s'", $c->getName(), $c->getName(), $c->getName()); // generate argument states
        }
        return $args;
    }

    public static function command_lazy_complete_function($cmd, $prefix = null, $name = null) {
        $buf = new Buffer;
        $buf->appendLine("local curcontext=\$curcontext state line ret=1");
        $buf->appendLine("declare -A opt_args");
        $buf->appendLine("local ret=1");

        $flags = self::command_flags($cmd);
        $args  = self::command_args($cmd);

        if ($flags || $args) {
            $buf->appendLine("_arguments -w -C -S -s \\");
            if ($flags) {
                foreach( $flags as $flag ) {
                    $buf->appendLine($flag . "\\");
                }
            }

            if ($args) {
                foreach( $args as $arg ) {
                    $buf->appendLine($arg . "\\");
                }
            }

            $buf->appendLine("&& ret=0");

            $buf->appendLines(self::command_args_case($cmd));
        }
        $buf->appendLine("return ret");

        if ($name) {
            $funcName = $name;
        } elseif ($prefix) {
            $funcName = $prefix . $cmd->getName();
        } else {
            $funcName =$cmd->getName();
        }
        return zsh_comp_function($funcName, $buf->__toString());
    }



    public static function complete_with_subcommands($programName, $cmd, $level = 1) {
        $subcmds = self::visible_commands($cmd->getCommandObjects());
        $descs  = Zsh::describe_commands($subcmds);
        $descs  = str_indent($descs, $level + 1);

        $code = array();

        // $code[] = 'echo $words[$CURRENT-1]';


        $code[] = "_arguments -C \\";
        if ($args = self::command_flags($cmd)) {
            // $code[] = indent($level + 1) . join( " \\\n" . indent($level + 1), $args) . " \\";
            $code[] = join_indent_continued($args, 1) . " \\";
        }

        $code[] = "': :->cmds' \\";
        $code[] = "'*:: :->option-or-argument' \\";
        $code[] = " && return";


        $code[] = "case \$state in";
        $code[] = indent($level) . "(cmds)";
        $code[] = $descs;
        $code[] = indent($level) . ";;";

        $code[] = "(option-or-argument)";


        // $code[] = "  curcontext=\${curcontext%:*:*}:$programName-\$words[1]:";
        // $code[] = "  case \$words[1] in";

        $code[] = "  curcontext=\${curcontext%:*}-\$line[1]:";
        $code[] = "  case \$line[1] in";


        foreach ($subcmds as $k => $subcmd) {
            // TODO: support alias
            $code[] = "(" . $k . ")";

            if ($subcmd->hasCommands()) {
                $code[] = self::complete_with_subcommands($programName, $subcmd, $level + 1);
            } else {
                $code[] = self::complete_command_options_arguments($subcmd, $level + 1);
            }
            $code[] = ";;";
        }

        $code[] = "  esac";
        $code[] = "  ;;";

        $code[] = "esac"; // close state
        return join_indent($code);
    }


}



