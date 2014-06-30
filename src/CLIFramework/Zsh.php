<?php
namespace CLIFramework;

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
        $specs = $cmd->getOptionSpecs();

        /*
        '(- 1 *)--version[display version and copyright information]' \
        '(- 1 *)--help[print a short help statement]' \
        */
        foreach ($specs->options as $opt ) {
            $args[] = self::option_flag_item($opt);
        }
        return $args;
    }



}



