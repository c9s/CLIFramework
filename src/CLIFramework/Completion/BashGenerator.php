<?php
namespace CLIFramework\Completion;
use CLIFramework\Buffer;
use Exception;
use CLIFramework\Application;
use CLIFramework\ArgInfo;

class BashGenerator
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

    public function __construct($app, $programName, $bindName, $compName)
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
            if ( ! preg_match('#^_#', $name) ) {
                $visible[$name] = $cmd;
            }
        }
        return $visible;
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
                        $str .= ':{' . join(' ', array($this->meta_command_name(), qq($placeholder), $cmdSignature, 'opt', $optName, 'valid-values')) . '}';
                    } elseif ($values = $opt->getValidValues()) {
                        // not callable, generate static array
                        $str .= ':(' . join(' ', array_qq($values)) . ')';
                    }
                } elseif ($opt->suggestions) {
                    if ( is_callable($opt->suggestions) ) {
                        $str .= ':{' . join(' ', array($this->meta_command_name(), qq($placeholder), $cmdSignature, 'opt', $optName, 'suggestions') ) . '}';
                    } elseif ($values = $opt->getSuggestions()) {
                        // not callable, generate static array
                        $str .= ':(' . join(' ', array_qq($values)) . ')';
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
}


