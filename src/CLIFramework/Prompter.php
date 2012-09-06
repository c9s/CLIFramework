<?php
namespace CLIFramework;
use CLIFramework\Formatter;

/**
 * Prompter class
 *
 *
 *
 */
class Prompter
{

    public $style;
    public $formatter;

    public function __construct()
    {
        $this->formatter = new Formatter;
    }

    /**
     * set prompt style
     */
    public function setStyle($style)
    {
        return $this->style = $style;
    }

    public function readline($prompt)
    {
        if ( extension_loaded('readline') ) {
            $answer = readline($prompt);
            readline_add_history($answer);
        } else {
            echo $prompt;
            $answer = rtrim( fgets( STDIN ), "\n" );
        }

        return trim( $answer );
    }

    /**
     * show prompt with message
     */
    public function ask($prompt, $validAnswers = null )
    {
        if ($validAnswers) {
            $prompt .= ' [' . join('/',$validAnswers) . ']';
        }
        $prompt .= ' ';

        if ($this->style) {
            echo $this->formatter->getStartMark( $this->style );
            // $prompt = $this->formatter->getStartMark( $this->style ) . $prompt . $this->formatter->getClearMark();
        }

        $answer = null;
        while (1) {
            $answer = $this->readline( $prompt );
            if ($validAnswers) {
                if ( in_array($answer,$validAnswers) ) {
                    break;
                } else {
                    continue;
                }
            }
            break;
        }
        if ($this->style) {
            echo $this->formatter->getClearMark();
        }

        return $answer;
    }
}
