<?php
namespace CLIFramework;
use CLIFramework\ValueCollection;

class ArgInfo
{
    public $name;

    public $isa;

    public $optional;

    public $multiple;

    public $suggestions;

    public $validValues;

    protected $validator;

    /* file/path glob pattern */
    public $glob;

    public function __construct($name, $desc = null)
    {
        $this->name = $name;
        if ($desc) {
            $this->desc = $desc;
        }
    }

    public function isa($isa) {
        $this->isa = $isa;
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

    public function multiple($a = true) {
        $this->multiple = $a;
        return $this;
    }

    public function validValues($val) {
        $this->validValues = $val;
        return $this;
    }

    public function validator($cb) {
        $this->validator = $cb;
        return $this;
    }

    /**
     * Assign suggestions
     *
     * @param string[]|string|Closure $value
     *
     * $value can be an array of strings, or a closure
     *
     * If $value is string, the prefix "zsh:" will be translated into zsh function call.
     */
    public function suggestions($values) {
        $this->suggestions = $values;
        return $this;
    }


    /**
     * Specify argument glob pattern
     */
    public function glob($g) {
        $this->glob = $g;
        return $this;
    }


    public function getSuggestions() {
        if ($this->suggestions) {
            if (is_callable($this->suggestions)) {
                return call_user_func($this->suggestions);
            }
            return $this->suggestions;
        }
    }


    public function getValidValues() {
        if ($this->validValues) {
            if (is_callable($this->validValues)) {
                return call_user_func($this->validValues);
            }
            return $this->validValues;
        }
    }

    /**
     * Test a value if it match the spec
     */
    public function validate($value) {
        if ($this->isa) {
            switch($this->isa) {
            case "number":
                return is_numeric($value);
            case "boolean":
                return is_bool($value);
            case "string":
                return is_string($value);
            }
        }
        $validValues = $this->getValidValues();
        if ($validValues && $validValues instanceof ValueCollection) {
            return $validValues->containsValue($value);
        } elseif ( is_array($validValues) ) {
            return in_array($value, $validValues);
        }
        if ($this->validator) {
            return call_user_func($this->validator);
        }
        return true;
    }

}


