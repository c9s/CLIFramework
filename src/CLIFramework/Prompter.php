<?php
namespace CLIFramework;
use CLIFramework\ServiceContainer;

/**
 * Prompter class
 */
class Prompter
{
    private $style;
    private $formatter;
    private $console;

    public function __construct(ServiceContainer $container = null)
    {
        $container = $container ?: ServiceContainer::getInstance();
        $this->formatter = $container['formatter'];
        $this->console = $container['console'];
    }

    /**
     * set prompt style
     */
    public function setStyle($style)
    {
        return $this->style = $style;
    }

    /**
     * show prompt with message
     */
    public function ask($prompt, $validAnswers = NULL, $default = NULL)
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
            $answer = trim($this->console->readLine( $prompt ));
            if ($validAnswers) {
                if (in_array($answer,$validAnswers) ) {
                    break;
                } else {
                    if (trim($answer) === "" && $default ) {
                        $answer = $default;
                        break;
                    }
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

    /**
     * Show password prompt with a message.
     */
    public function password($prompt)
    {
        if ($this->style) {
            echo $this->formatter->getStartMark( $this->style );
        }

        $result = $this->console->readPassword($prompt);

        if ($this->style) {
            echo $this->formatter->getClearMark();
        }

        return $result;
    }
}
