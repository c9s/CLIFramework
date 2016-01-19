<?php
namespace CLIFramework\Extension;
use CLIFramework\ServiceContainer;
use CLIFramework\Command;
use CLIFramework\CommandBase;
use CLIFramework\Logger;
use GetOptionKit\OptionCollection;
use LogicException;
use CLIFramework\ArgInfoList;

abstract class ExtensionBase
{
    protected $container;

    public function __construct()
    {

    }

    public function setServiceContainer(ServiceContainer $container)
    {
        $this->container = $container;
    }


    /**
     * init method is called when the extension is added to the pool.
     */
    public function init()
    {

    }

    static public function isSupported()
    {
        return true;
    }

    public function isAvailable()
    {
        return true;
    }

    public function options($opts)
    {
    }

    public function arguments($args) 
    {

    }

    public function prepare() 
    {

    }

    public function execute() 
    {

    }

    public function finish() 
    {

    }

    public function __get($accessor) 
    {
        if (isset($this->container[$accessor])) {
            return $this->container[$accessor];
        }
        throw new LogicException("Undefined accessor '$accessor'");
    }

}




