<?php
namespace CLIFramework\ReadLine\Completer;

interface Completer {
    /**
     * Check if the context matches this completer's context
     *
     * @param string $input
     * @param string $token
     * @param integer $index
     */
    public function canComplete($input, $token, $index);

    /**
     * @param string $input
     * @param string $token
     * @param integer $index
     */
    public function complete($input, $token, $index);

}

