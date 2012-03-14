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

    /**
     * show prompt with message
     */
    public function ask($prompt, $validAnswers = null )
    {
        if( $validAnswers ) {
            $prompt .= ' [' . join('/',$validAnswers) . ']';
        }
        $prompt .= ': ';

        $answer = null;
        while(1) {
            if( extension_loaded('readline') ) {
                $answer = readline($prompt);
                readline_add_history($answer);
            } else {
                echo $prompt;
                $answer = rtrim( fgets( STDIN ), "\n" );
            }
            $answer = trim( $answer );
            if( $validAnswers ) {
                if( in_array($answer,$validAnswers) ) {
                    break;
                } else {
                    continue;
                }
            }
            break;
        }
        return $answer;
    }

    public function choose($prompt, $choices ) 
    {


    }

}


