<?php
namespace CLIFramework\IO;

/**
 * Console utilities using readline.
 */
class ReadlineConsole implements Console
{
    /**
     * @var Stty
     */
    private $stty;

    public function __construct(Stty $stty)
    {
        $this->stty = $stty;
    }

    public static function isAvailable()
    {
        return extension_loaded('readline');
    }

    public function readLine($prompt)
    {
        $line = $this->doReadLine($prompt);
        readline_add_history($line);
        return $line;
    }

    public function readPassword($prompt)
    {
        return $this->noEcho(function() use ($prompt) {
            return $this->doReadLine($prompt);
        });
    }

    public function noEcho(\Closure $callback)
    {
        return $this->stty->withoutEcho($callback);
    }

    private function doReadLine($prompt)
    {
        return readline($prompt);
    }
}
