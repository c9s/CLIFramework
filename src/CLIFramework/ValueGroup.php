<?php
namespace CLIFramework;
use ArrayObject;

class ValueGroup extends ArrayObject
{

    public function append($val) { 
        parent::append($val);
        return $this;
    }


    public function add($val) {
        parent::append($val);
        return $this;
    }
}


