<?php
namespace CLIFramework\IO;

interface Console
{
    /**
     * Read a line from user input.
     * @return string
     */
    public function readLine($prompt);

    /**
     * Read a line from user input without echoing if possible.
     * @return string
     */
    public function readPassword($prompt);

    /**
     * Turn off echo and execute the callback function.
     * @param \Closure $callback the callback function to execute.
     * @return mixed return the result value returned by the callback.
     */
    public function noEcho(\Closure $callback);
}
