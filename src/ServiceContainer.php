<?php
namespace CLIFramework;
use Pimple\Container;
use CLIFramework\Logger;
use CLIFramework\CommandLoader;
use CLIFramework\Config\GlobalConfig;
use CLIFramework\IO\StreamWriter;
use CLIFramework\IO\NullStty;
use CLIFramework\IO\UnixStty;
use CLIFramework\IO\ReadlineConsole;
use CLIFramework\IO\StandardConsole;
use Universal\Event\PhpEvent;


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
        $that = $this;
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
            return null;
        };

        $this['event'] = function() {
            return new PhpEvent;
        };

        $this['config'] = function($c) {
            if (empty($c['config.path']) || !$c['config.path']) {
                return new GlobalConfig(array());
            }
            return new GlobalConfig(parse_ini_file($c['config.path'], true));
        };
        $this['writer'] = function($c) {
            // return new StreamWriter(STDOUT);
            $output = fopen("php://output", "w");
            return new StreamWriter($output);
        };
        $this['logger'] = function($c) use ($that) {
            return new Logger($that);
        };
        $this['formatter'] = function($c) {
            return new Formatter;
        };
        $this['console.stty'] = function($c) use ($that){
            if ($that->isWindows()) {
                // TODO support Windows
                return new NullStty();
            }
            return new UnixStty();
        };
        $this['console'] = function($c) {
            if (ReadlineConsole::isAvailable()) {
                return new ReadlineConsole($c['console.stty']);
            }
            return new StandardConsole($c['console.stty']);
        };
        $this['command_loader'] = function($c) {
            return CommandLoader::getInstance();
        };
        parent::__construct();
    }

    public function isWindows()
    {
        return preg_match('/^Win/', PHP_OS);
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


