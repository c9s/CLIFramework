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



    /**
     *
     *
     */
    public function choose($prompt, $choices ) 
    {
        echo $prompt . ": \n";

        $choicesMap = array();
        $i = 0;
        foreach( $choices as $choice => $value ) {
            $i++;
            $choicesMap[ $i ] = $value;
            echo "\t" . ($i) . "  $choice\n";
        }

        $choosePrompt = "Please Choose (1-$i): ";
        while(1) {
            if( extension_loaded('readline') ) {
                $answer = readline($choosePrompt);
                readline_add_history($answer);
            } else {
                echo $choosePrompt;
                $answer = rtrim( fgets( STDIN ), "\n" );
            }

            $answer = (int) trim( $answer );
            if( is_integer( $answer ) ) {
                if( isset( $choicesMap[$answer] ) ) {
                    return $choicesMap[$answer];
                } else {
                    continue;
                }
            }
            break;
        }
    }

}


