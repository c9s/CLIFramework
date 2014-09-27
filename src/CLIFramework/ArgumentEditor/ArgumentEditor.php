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
        $this->args[] = trim($arg);
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

    public function escape() {
        $this->args = array_map(function($arg) {
            return escapeshellarg($arg);
        }, $this->args);
    }


    public function __toString() {
        return join(' ', $this->args);
    }


}

