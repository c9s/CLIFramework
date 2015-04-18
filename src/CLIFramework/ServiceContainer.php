<?php
namespace CLIFramework;
use Pimple\Container;
use CLIFramework\Logger;

class ServiceContainer extends Container
{
    public function __construct()
    {
        $this['logger'] = function($c) {
            return Console::getInstance()->getLogger();
        };
        parent::__construct();
    }

    static public function getInstance()
    {
        static $instance;
        $instance = new self;
        return $instance;
    }
}


