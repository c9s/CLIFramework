<?php
namespace CLIFramework\Extension;
use CLIFramework\ServiceContainer;
use CLIFramework\Command;
use CLIFramework\CommandBase;
use CLIFramework\Logger;

abstract class ExtensionBase
{
    protected $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    public function isAvailable()
    {
        return true;
    }
}




