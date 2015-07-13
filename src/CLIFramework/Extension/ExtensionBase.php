<?php
namespace CLIFramework\Extension;
use CLIFramework\ServiceContainer;
use CLIFramework\Command;
use CLIFramework\CommandBase;
use CLIFramework\Logger;
use GetOptionKit\OptionCollection;

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

    public function options(OptionCollection $opts)
    {
    }

}




