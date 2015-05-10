<?php
namespace CLIFramework\IO;

/**
 * Console utilities using readline.
 */
class ReadlineConsole implements Console
{
    /**
     * Whether to show prompt or not.
     * @var boolean
     */
    private $prompting;

    /**
     * Used for buffering characters read from readline.
     * @var string
     */
    private $buffer;

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
        $this->prepareReadLine();
        $this->doReadLine($prompt);
        $line = $this->finishReadLine();
        readline_add_history($line);
        return $line;
    }

    public function readPassword($prompt)
    {
        return $this->noEcho(function() use ($prompt) {
            $this->prepareReadLine();
            $this->doReadLine($prompt);
            return $this->finishReadLine();
        });
    }

    public function noEcho(\Closure $callback)
    {
        return $this->stty->withoutEcho($callback);
    }

    protected function getReadStream()
    {
        return STDIN;
    }

    protected function getTvSec()
    {
        return NULL;
    }

    protected function getTvUsec()
    {
        return 0;
    }

    private function prepareReadLine()
    {
        $this->buffer = '';
        $this->prompting = true;
    }

    private function doReadLine($prompt)
    {
        if ($this->isCallbackHandlerAvailable()) {
            $this->readLineWithTimeout($prompt);
            return;
        }

        $this->readLineWithoutTimeout($prompt);
    }

    private function readLineWithoutTimeout($prompt)
    {
        $this->buffer = readline($prompt);
    }

    private function readLineWithTimeout($prompt)
    {
        readline_callback_handler_install($prompt, array($this, 'onRead'));
        while ($this->prompting) {
            $read = array($this->getReadStream());
            $write = NULL;
            $except = NULL;
            $tvSec = $this->getTvSec();
            $tvUsec = $this->getTvUsec();
            $isSuccess = stream_select($read, $write, $except, $tvSec, $tvUsec);

            if (!$isSuccess) {
                break;
            }

            if ($isSuccess && in_array($this->getReadStream(), $read)) {
                readline_callback_read_char();
            }
        }
    }

    private function finishReadLine()
    {
        $line = empty($this->buffer) ? '' : $this->buffer;
        $this->buffer = '';
        return $line;
    }

    private function onRead($line)
    {
        if ($this->isCallbackHandlerAvailable()) {
            readline_callback_handler_remove();
        }
        $this->prompting = false;
        $this->buffer = $line;
    }

    private function isCallbackHandlerAvailable()
    {
        return function_exists('readline_callback_handler_install');
    }
}
