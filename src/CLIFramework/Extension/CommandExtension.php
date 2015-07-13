<?php
namespace CLIFramework\Extension;
use CLIFramework\Command;
use CLIFramework\Extension\Extension;
use CLIFramework\Extension\ExtensionBase;

abstract class CommandExtension extends ExtensionBase
{
    protected $config;

    protected $command;

    public function bindCommand(Command $command)
    {
        $this->command = $command;
        $this->options($command->getOptionCollection());
        // $this->arguments( );

        $this->config = $command->getApplication()->getGlobalConfig();
        $this->setServiceContainer($command->getApplication()->getService());
        $this->init();
    }

}
