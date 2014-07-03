<?php
namespace CLIFramework;
use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;
use ArrayObject;

class ArgumentInfoList extends ArrayObject {

    public function add($name) {
        $arginfo = new ArgumentInfo($name);
        $this->append($arginfo);
        return $arginfo;
    }

}




