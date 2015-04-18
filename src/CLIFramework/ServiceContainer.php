<?php
namespace CLIFramework;
use Pimple\Container;
use CLIFramework\Logger;
use CLIFramework\CommandLoader;


/**
 *
 * Provided services:
 *
 *    logger:  CLIFramework\Logger
 *    formatter: CLIFramework\Formatter
 *    command_loader: CLIFramework\CommandLoader
 *
 * Usage:
 *
 *    $container = ServiceContainer::getInstance();
 *    $logger = $container['logger'];
 *
 */
class ServiceContainer extends Container
{
    public function __construct()
    {
        $this['logger'] = function($c) {
            return new Logger;
        };
        $this['formatter'] = function($c) {
            return new Formatter;
        };

        $this['command_loader'] = function($c) {
            return CommandLoader::getInstance();
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


