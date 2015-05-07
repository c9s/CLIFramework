<?php
namespace CLIFramework;
use Pimple\Container;
use CLIFramework\Logger;
use CLIFramework\CommandLoader;
use CLIFramework\IO\StreamWriter;


/**
 *
 * Provided services:
 *
 *    logger:  CLIFramework\Logger
 *    formatter: CLIFramework\Formatter
 *    command_loader: CLIFramework\CommandLoader
 *    writer: CLIFramework\IO\Writer
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
        $this['writer'] = function($c) {
            return new StreamWriter(STDOUT);
        };
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

        if (!$instance) {
            $instance = new static;
        }

        return $instance;
    }
}


