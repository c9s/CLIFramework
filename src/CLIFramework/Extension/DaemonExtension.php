<?php
namespace CLIFramework\Extension;

use CLIFramework\ServiceContainer;
use CLIFramework\Command;
use CLIFramework\Logger;
use CLIFramework\Exception\ExtensionException;
use CLIFramework\IO\StreamWriter;

class DaemonExtension extends ExtensionBase
{
    private $config;
    private $logger;
    private $isNoClose = false;
    private $isNoChangeDirectory = false;
    private $command;

    public function __construct()
    {
        $container = ServiceContainer::getInstance();
        $this->config = $container['config'];
    }

    public static function isAvailable()
    {
        return function_exists('pcntl_fork');
    }

    public function bind(Command $command)
    {
        $this->command = $command;
        $this->bindOptions($command);
        $this->bindHooks($command);
    }

    public function run()
    {
        if (!$this->isAvailable()) {
            throw new ExtensionException("pcntl_fork() is not supported.");
        }

        $this->prepareLogger();
        $logger = $this->getLogger();
        $logger->debug('call hook: run.before');
        $this->callHook('run.before');
        $logger->debug('call daemonize()');
        $this->daemonize();
        $logger->debug('call hook: run.after');
        $this->callHook('run.after');
        $logger->debug('isNoClose = ' . var_export($this->isNoClose, true));
        $logger->debug('isNoChangeDirectory = ' . var_export($this->isNoChangeDirectory, true));
        $logger->debug('PidFilePath = ' . $this->getPidFilePath());
    }

    public function noClose()
    {
        $this->isNoClose = true;
    }

    public function noChangeDirectory()
    {
        $this->isNoChangeDirectory = true;
    }

    public function getPidFilePath()
    {
        $pid = getmypid();
        $pidFileName = $this->command ? $this->command->getName() : $pid;
        return $this->config->getPidDirectory() . "/$pidFileName.pid";
    }

    private function bindHooks($command)
    {
        $extension = $this;
        $command->addHook('execute.before', function() use ($extension) {
            $extension->run();
        });
        $command->addHook('execute.after', function() use ($extension) {
            @unlink($extension->getPidFilePath());
        });
    }

    private function bindOptions($command)
    {
        $options = $command->getOptionCollection();
        if (!$options) {
            return;
        }
        $options->add('pid-file', 'The path of pid file.');
    }

    private function prepareLogger()
    {
        $logPath = $this->getLogPath();
        $logger = $this->getLogger();

        if (!$logPath || !$logger) {
            return;
        }

        $resource = fopen($logPath, "a+");

        if ($resource === false) {
            throw new ExtensionException("Can't open file: $logPath");
        }

        // TODO change logging style
        $logger->setWriter(new StreamWriter($resource));
    }

    private function daemonize()
    {
        switch (pcntl_fork()) {
        case -1:
            throw new ExtensionException("pcntl_fork() failed");
        case 0:
            break;
        default:
            exit(0);
        }

        if (!$this->createPidFile()) {
            throw new ExtensionException("creating a pid file failed");
        }

        if (!$this->isNoChangeDirectory) {
            $this->changeDirectory();
        }

        if (!$this->isNoClose) {
            $this->closeFileDescriptors();
        }

    }

    private function changeDirectory()
    {
        if (!$this->isNoChangeDirectory && !chdir("/")) {
            throw new ExtensionException("chdir failed");
        }
    }

    private function closeFileDescriptors()
    {
        if (!fclose(STDIN)) {
            throw new ExtensionException("fclose(STDIN) failed");
        }

        if (!fclose(STDOUT)) {
            throw new ExtensionException("fclose(STDOUT) failed");
        }

        if (!fclose(STDERR)) {
            throw new ExtensionException("fclose(STDERR) failed");
        }
    }

    private function createPidFile()
    {
        return file_put_contents($this->getPidFilePath(), getmypid());
    }

    private function getLogPath()
    {
        if (!$this->hasApplication()) {
            return null;
        }
        $options = $this->command->getApplication()->getOptions();
        return $options && $options->{'log-path'} ? $options->{'log-path'} : null;
    }

    private function getLogger()
    {
        if ($this->hasApplication()) {
            $this->logger = $this->command->getLogger();
        }

        if (!$this->logger) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    private function hasApplication()
    {
        return $this->command && $this->command->hasApplication();
    }
}
