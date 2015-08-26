<?php
namespace CLIFramework\Extension;
use CLIFramework\ServiceContainer;
use CLIFramework\Command;
use CLIFramework\CommandBase;
use CLIFramework\Logger;
use CLIFramework\Exception\ExtensionException;
use CLIFramework\Extension\CommandExtension;
use CLIFramework\IO\StreamWriter;
use GetOptionKit\OptionCollection;

class DaemonExtension extends CommandExtension
{
    protected $logger;

    /**
     * @var boolean Detach from shell.
     */
    protected $detach = false;

    protected $chdir = false;

    public function isAvailable()
    {
        return function_exists('pcntl_fork');
    }


    static public function isSupported()
    {
        return function_exists('pcntl_fork');
    }


    public function finish()
    {
        $pidFile = $this->getPidFilePath();
        if (file_exists($pidFile)) {
            @unlink($pidFile);
        }
    }

    public function execute()
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
    public function detach()
    {
        $this->detach = true;
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

    public function options($opts)
    {
        $opts->add('pid-file?', '(daemon) Path of pid file.');
        $opts->add('log-path?', '(daemon) Path of log file when running with daemon extension.');
        $opts->add('detach', '(daemon) Detach from the shell.');
    }

    protected function prepareLogger()
    {
        $logPath = $this->getLogPath();
        $logger = $this->getLogger();

        if (!$logPath || !$logger) {
            return;
        }

        $resource = fopen($logPath, "a+");
        if ($resource === false) {
            throw new ExtensionException("Can't open file: $logPath", $this);
        }

        // TODO change logging style
        $logger->setWriter(new StreamWriter($resource));
    }

    protected function daemonize()
    {
        if ($this->detach || $this->command->options->{'detach'}) {
            $this->command->logger->debug('forking process to background..');
            // The return value of pcntl_fork: 
            //
            // On success, the PID of the child process is returned in the parent's
            // thread of execution, and a 0 is returned in the child's thread of
            // execution. On failure, a -1 will be returned in the parent's
            // context, no child process will be created, and a PHP error is
            // raised.
            switch (pcntl_fork()) {
            case -1:
                throw new ExtensionException("pcntl_fork() failed");

            // child process
            case 0:
                break;

            // exit parent process
            default:
                if (!fclose(STDIN)) {
                    throw new ExtensionException("fclose(STDIN) failed");
                }

                if (!fclose(STDOUT)) {
                    throw new ExtensionException("fclose(STDOUT) failed");
                }

                if (!fclose(STDERR)) {
                    throw new ExtensionException("fclose(STDERR) failed");
                }
                exit(0);
            }
        }

        // The execution here runs in child process
        if ($this->savePid() === false) {
            throw new ExtensionException("pid file create failed");
        }

        if ($this->chdir) {
            $this->chdir();
        }
    }

    private function chdir()
    {
        if ($this->chdir && !chdir("/")) {
            throw new ExtensionException("chdir failed");
        }
    }

    protected function savePid()
    {
        $pidFile = $this->getPidFilePath();
        $pid = getmypid();
        $this->command->logger->debug("pid {$pid} saved in $pidFile");
        return file_put_contents($pidFile, $pid);
    }

    protected function getLogPath()
    {
        // var_dump( $this->command ); 
        if ($logPath = $this->command->options->{'log-path'}) {
            return $logPath;
        }

        if ($options = $this->getApplicationOptions()) {
            if ($logPath = $options->{'log-path'}) {
                return $logPath;
            }
        }
        return null;
    }

    protected function getLogger()
    {
        if ($this->hasApplication()) {
            $this->logger = $this->command->getLogger();
        }
        if (!$this->logger) {
            $this->logger = new Logger();
        }
        return $this->logger;
    }

    protected function getApplicationOptions()
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
