<?php
namespace CLIFramework;
use Pimple\Container;
use CLIFramework\Logger;
use CLIFramework\CommandLoader;
use CLIFramework\IO\StreamWriter;
use CLIFramework\Config\GlobalConfig;


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
        $this['config.path'] = function($c) {
            $filename = 'cliframework.ini';
            $configAtCurrentDirectory = getcwd() . DIRECTORY_SEPARATOR . $filename;
            $configAtHomeDirectory = getenv('HOME') . DIRECTORY_SEPARATOR . $filename;

            if (file_exists($configAtCurrentDirectory)) {
                return $configAtCurrentDirectory;
            }

            if (file_exists($configAtHomeDirectory)) {
                return $configAtHomeDirectory;
            }

            return '';
        };
        $this['config'] = function($c) {
            if (empty($c['config.path'])) {
                return new GlobalConfig(array());
            }
            return new GlobalConfig(parse_ini_file($c['config.path'], true));
        };
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


