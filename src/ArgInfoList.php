<?php
namespace CLIFramework;
use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;
use ArrayObject;

#[\AllowDynamicProperties]
class ArgInfoList extends ArrayObject {

    public function add($name) {
        $arginfo = new ArgInfo($name);
        $this->append($arginfo);
        return $arginfo;
    }

}




