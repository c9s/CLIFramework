<?php
namespace CLIFramework;

use CLIFramework\Formatter;

/**
 * Prompter class
 *
 *
 *
 */
class Chooser
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

    /**
     *
     *
     */
    public function choose($prompt, $choices)
    {
        echo $prompt . ": \n";

        $choicesMap = array();
        $isAssoc = (array_keys($choices) !== range(0, count($choices) - 1));

        $i = 0;
        if ($isAssoc) {
            foreach ($choices as $choice => $value) {
                $i++;
                $choicesMap[ $i ] = $value;
                echo "\t$i: " . $choice . " => " . $value . "\n";
            }
        } else {
            //is sequential
            foreach ($choices as $choice) {
                $i++;
                $choicesMap[ $i ] = $choice;
                echo "\t$i: $choice\n";
            }
        }

        if ($this->style) {
            echo $this->formatter->getStartMark($this->style);
        }

        $completionItems = array_keys($choicesMap);
        $choosePrompt = "Please Choose 1-$i > ";
        while (1) {
            if (extension_loaded('readline')) {
                $success = readline_completion_function(function ($string, $index) use ($completionItems) {
                    return $completionItems;
                });
                $answer = readline($choosePrompt);
                readline_add_history($answer);
            } else {
                echo $choosePrompt;
                $answer = rtrim(fgets(STDIN), "\n");
            }

            $answer = (int) trim($answer);
            if (is_integer($answer)) {
                if (isset($choicesMap[$answer])) {
                    if ($this->style) {
                        echo $this->formatter->getClearMark();
                    }

                    return $choicesMap[$answer];
                } else {
                    continue;
                }
            }
            break;
        }
    }
}
