<?php
namespace CLIFramework\Exception;
use Exception;

class ExtensionException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
