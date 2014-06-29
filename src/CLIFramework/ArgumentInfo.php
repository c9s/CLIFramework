<?php
namespace CLIFramework;

class ArgumentInfo
{
    public $name;

    public $type;

    public $optional;

    public function __construct($name, $desc = null)
    {
        $this->name = $name;
        if ($desc) {
            $this->desc = $desc;
        }
    }

    public function type($type) {
        $this->type = $type;
        return $this;
    }

    public function desc($desc) {
        $this->desc = $desc;
        return $this;
    }

    public function optional($optional = true) {
        $this->optional = $optional;
        return $this;
    }

    /**
     * Test a value if it match the spec
     */
    public function test($value) {
        if ($this->type) {
            switch($this->type) {
            case "number":
            case "numeric":
                return is_numeric($value);
            case "boolean":
            case "bool":
                return is_bool($value);
            case "string":
                return is_string($value);
            }
        }
        return true;
    }

}


