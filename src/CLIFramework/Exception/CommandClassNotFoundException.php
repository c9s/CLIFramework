<?php
namespace CLIFramework\Exception;
use Exception;

class CommandClassNotFoundException extends Exception
{
    public $class;

    public $registeredNamespaces = array();

    public $possibleClasses = array();

    public function __construct($class, $registeredNamespaces = array()) {
        $this->class = $class;
        $this->registeredNamespaces = $registeredNamespaces;

        $this->possibleClasses[] = $class;
        foreach( $registeredNamespaces as $ns ) {
            $this->possibleClasses[] = $ns . '\\' . ltrim($class, '\\');
        }

        $desc = "Command $class not found.";
        if (!empty($this->registeredNamespaces)) {
            $desc .= "\nRegistered namespaces: [" . join(',', $this->registeredNamespaces) . "]";
        }
        if (!empty($this->possibleClasses)) {
            $desc .= "\nPossible classnames: [" . join(',', $this->possibleClasses) . "]";
        }
        parent::__construct($desc);
    }

    public function getRegisteredNamespaces() {
        return $this->registeredNamespaces;
    }

    public function getPossibleClasses() {
        return $this->possibleClasses;
    }
}


