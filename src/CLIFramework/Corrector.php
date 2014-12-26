<?php
namespace CLIFramework;

/**
 * Try to correct/suggest user's input
 *
 * @method __construct
 * @method guess
 * @method match
 */
class Corrector
{
    protected $possibleTokens = array();

    /**
     * Constructor.
     *
     * @param string[] $possibleTokens candidates of the suggestion
     */
    public function __construct(array $possibleTokens = array())
    {
        $this->possibleTokens = $possibleTokens;
    }

    /**
     * Given user's input, ask user to correct it.
     *
     * @param string $input user's input
     * @return string corrected input
     */
    public function correct($input) {
        $guess = $this->match($input);
        if ($guess === $input)
            return $guess;
        else
            return $this->askForGuess($guess) ? $guess : $input;
    }

    /**
     * Given user's input, return the best match among candidates.
     *
     * @param string $input @see self::correct()
     * @return string best matched string or raw input if no candidates provided
     */
    public function match($input)
    {
        if (empty($this->possibleTokens))
            return $input;

        $bestSimilarity = -1;
        $bestGuess = $input;
        foreach ($this->possibleTokens as $possibleToken) {
            similar_text($input, $possibleToken, $similarity);
            if ($similarity > $bestSimilarity) {
                $bestSimilarity = $similarity;
                $bestGuess = $possibleToken;
            }
        }
        return $bestGuess;
    }

    private function askForGuess($guess)
    {
        $prompter = new Prompter;
        $answer = $prompter->ask("Did you mean '$guess'?", array('Y','n'), 'Y');
        return !$answer || strtolower($answer) == 'y';
    }

}
