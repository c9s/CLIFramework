<?php
namespace CLIFramework\ArgumentEditor;

class ArgumentEditor
{
    public $args = array();

    public function __construct($args = array())
    {
        $this->args = $args;
    }

    public function append($arg) {
        $args = func_get_args();
        foreach($args as $arg) {
            $this->args[] = trim($arg);
        }
        return $this;
    }

    public function remove($arg) {
        $p = 0;
        $removed = 0;
        while($p !== false) {
            // search next
            $p = array_search($arg, $this->args);
            if ($p !== false) {
                array_splice($this->args, $p);
                $removed++;
            }
        }
        return $removed;
    }

    public function replace($needle, $newarg) {
        $p = array_search($needle, $this->args);
        if ($p !== false) {
            $spliced = array_splice($this->args, $p, 1, $newarg);
            return $spliced[0];
        }
        return false;
    }

    public function replaceRegExp($regexp, $newarg) {
        $regexp = '/' . preg_quote($regexp, '/') . '/';
        $this->args = preg_replace($regexp, $newarg, $this->args);
    }


    /**
     * Remove arguments by regular expression pattern
     *
     * @param string $regexp
     */
    public function removeRegExp($regexp) {
        $regexp = '/' . preg_quote($regexp, '/') . '/';
        $this->args = preg_grep($regexp, $this->args, PREG_GREP_INVERT);
    }

    /**
     * Filter all arguments through a closure
     *
     * @param Closue 
     */
    public function filter($callback) {
        $this->args = array_map($callback, $this->args);
        return $this;
    }


    /**
     * Run escape fitler to current arguments
     */
    public function escape() {
        $this->args = array_map(function($arg) {
            return escapeshellarg($arg);
        }, $this->args);
    }


    /**
     * Output current argument to string.
     */
    public function __toString() {
        return join(' ', $this->args);
    }


}

