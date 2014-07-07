<?php
namespace CLIFramework\ReadLine\Completer;


class BaseCompleter
{
    public $options = array();


    public function __construct($options = array()) {
        $this->options = $options;
    }

    public function match($prefix, $matches = array() ) {
        $list = array_filter($matches, function($match) use ($prefix) {
            return strpos($match, $prefix) === 0;
        });
        return $list;
    }


}


