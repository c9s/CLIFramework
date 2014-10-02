<?php
namespace CLIFramework\Exception;
use Exception;

class CommandClassNotFoundException extends Exception
{
    public $class;

    public $registeredNamespaces;

    public function __construct($class, $registeredNamespaces = array()) {
        $this->class = $class;
        $this->registeredNamespaces = $registeredNamespaces;
        $desc = "Command $class not found.";
        if (!empty($registeredNamespaces)) {
            $desc .= "Registered namespaces: [" . join(',', $registeredNamespaces) . "]";
        }
        parent::__construct($desc);
    }
}


