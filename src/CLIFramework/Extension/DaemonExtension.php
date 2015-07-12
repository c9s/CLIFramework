<?php
namespace CLIFramework\Extension;
use CLIFramework\ServiceContainer;
use CLIFramework\Command;
use CLIFramework\CommandBase;
use CLIFramework\Logger;
use CLIFramework\Exception\ExtensionException;
use CLIFramework\Extension\CommandExtension;
use CLIFramework\IO\StreamWriter;

class DaemonExtension extends ExtensionBase 
    implements CommandExtension
{
    private $config;

    private $logger;

    private $detach = true;

    private $chdir = false;

    private $command;

    public static function isAvailable()
    {
        return function_exists('pcntl_fork');
    }

    public function bindCommand(Command $command)
    {
        $this->command = $command;
        $this->bindOptions($command);
    }

    public function run()
    {
        if (!$this->isAvailable()) {
            throw new ExtensionException("pcntl_fork() is not supported.");
        }
        $this->prepareLogger();
        $logger = $this->getLogger();
        $this->daemonize();
        // $logger->debug('PidFilePath = ' . $this->getPidFilePath());
    }

    /**
     * Call this method if you don't want to close STDIN, STDOUT and STDERR on making a daemon process.
     */
    protected function noDetach()
    {
        $this->detach = false;
    }

    /**
     * Call this method if you want to change the current directory on making a daemon process.
     */
    protected function changeDirectory()
    {
        $this->chdir = true;
    }

    public function getPidFilePath()
    {
        if ($this->getCommandOptions() && $this->getCommandOptions()->{'pid-file'}) {
            return $this->getCommandOptions()->{'pid-file'};
        }
        $pid = getmypid();
        $pidFile = $this->command ? $this->command->getName() : $pid;
        return $this->config->getPidDirectory() . "/$pidFile.pid";
    }

    /*
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
    */

    private function bindOptions(Command $command)
    {
        $options = $command->getOptionCollection();
        $options->add('pid-file?', 'The path of pid file.');
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

        if ($this->chdir) {
            $this->chdir();
        }

        if ($this->detach) {
            $this->detach();
        }

    }

    private function chdir()
    {
        if ($this->chdir && !chdir("/")) {
            throw new ExtensionException("chdir failed");
        }
    }

    private function detach()
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
        $options = $this->getApplicationOptions();
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

    private function getApplicationOptions()
    {
        if (!$this->hasApplication()) {
            return null;
        }
        return $this->command->getApplication()->getOptions();
    }

    private function getCommandOptions()
    {
        return $this->command ? $this->command->getOptions() : null;
    }

    private function hasApplication()
    {
        return $this->command && $this->command->hasApplication();
    }
}
