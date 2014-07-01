<?php
namespace CLIFramework;

function indent($level) {
    return str_repeat('  ', $level);
}

class Zsh
{

    public static function indent_str($content, $level = 1) {
        $space = str_repeat('  ', $level);
        $lines = explode("\n", $content);
        $lines = array_map(function($line) use ($space) { return $space . $line; }, $lines);
        return join("\n", $lines);
    }

    public static function indent_array($lines, $level = 1) {
        $space = str_repeat('  ', $level);
        return array_map(function($line) use ($space) {
            return $space . $line;
        }, $lines);
    }



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
        $code .= join("\n", self::indent_array($array, 1) );
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
        $str = "'";
        if ($opt->short && $opt->long) {
            $str .= "(-" . $opt->short . " --" . $opt->long . ")'";
            $str .= "{-" . $opt->short . ',' . '--' . $opt->long . "}" . "'";
        } else if ($opt->long) {
            $str .= "--" . $opt->long;
        } else if ($opt->short) {
            $str .= "-" . $opt->short;
        }
        $str .= "[" . $opt->description . "]";

        // TODO: translate arginfo type into zsh completion type
        /*
        if ($opt->valueType) {
            $str .= ":" . $opt->valueType;
        }
        */
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
        foreach($arginfos as $arginfo) {

            /*
            '1:issue-status:->issue-statuses' \
            '2:: :_github_users' \
            */
            // $str = "'" . $idx++ . ":" . $arginfo->name . "'";
            $str = sprintf("':%s:->%s'", $arginfo->name, $arginfo->name);

            // TODO: translate arginfo type into zsh completion type
            // TODO: translate argument valid values into zsh/bash functions so that we 
            //       can hook it with zsh completion 
            /*
            if ($arginfo->type) {
                $str .= ':' . $arginfo->type;
            }
            */
            $args[] = $str;
        }
        return $args;
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
        return $args;
    }


    public static function complete_subcommands($mainCmd, $level = 1) {
        $cmds = self::visible_commands($mainCmd->getCommandObjects());
        $descs  = Zsh::describe_commands($cmds);
        $descs  = Zsh::indent_str($descs, $level + 1);

        $code = array();

        $code[] = "_arguments -C \
'1:cmd:->cmds' \
'*::arg:->args' \
&& ret=0";

        $code[] = "case \"\$state\" in";
        $code[] = indent($level) . "(cmds)";
        $code[] = $descs;
        $code[] = indent($level) . ";;";

        $code[] = "(args)";
        $code[] = "case \$words[{$level}] in";

        foreach ($cmds as $k => $cmd) {
            $_args  = self::indent_array(self::command_args($cmd), $level);

            // XXX: support alias
            $_flags = self::indent_array(self::command_flags($cmd), $level);
            $code[] = "(" . $k . ")";

            /* TODO: get argument spec from command class -> execute method 
             * to the argument spec below:
             *
                _arguments \
                    '1: :_github_users' \
                    '2: :_github_branches' \
                    && ret=0
            */
            if (!empty($_flags) || !empty($_args) ) {
                $code[] = indent($level) . "_arguments -s -w : \\";
                if (!empty($_args))
                    $code[] = indent($level + 1) . join( " \\\n" . indent($level + 1),$_args) . " \\";
                if (!empty($_flags))
                    $code[] = indent($level + 1) . join( " \\\n" . indent($level + 1),$_flags) . " \\";
                $code[] = indent($level + 1) . " && ret=0";
            }
            $code[] = ";;";
        }

        $code[] = "esac";
        $code[] = ";;";

        $code[] = "esac"; // close state
        return join("\n", $code);
    }


}



