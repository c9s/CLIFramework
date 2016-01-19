<?php
namespace CLIFramework\Exception;
use CLIFramework\Extension\ExtensionBase;
use Exception;

class ExtensionException extends \Exception
{
    protected $extension;

    public function __construct($message, ExtensionBase $extension = null)
    {
        parent::__construct($message);

        $this->extension = $extension;
    }

    public function getExtension()
    {
        return $this->extension;
    }
}
