<?php
namespace CLIFramework;
use CLIFramework\Command;

class ChainedCommand extends Command
{
    /**
     * @var array Command class names
     */
    public $commands = array();

    public function options($opts) 
    {
        $cmds = $this->getChainedCommands();
        foreach($cmds as $cmd)
            $cmd->options($opts);
    }

    public function getChainedCommands() 
    {
        $cmds = array();
        foreach( $this->commands as $command ) {
            $cmd = new $command($this->application);
            $cmd->logger = $this->logger;
            $cmds[] = $cmd;
        }
        return $cmds;
    }


    public function execute() 
    {
        $this->logger->info('Executing chained commands: ' . join(',', $this->commands ));
        $args = func_get_args();
        $cmds = $this->getChainedCommands();
        foreach( $cmds as $cmd ) {
            $cmd->options = $this->options;
            $cmd->executeWrapper($args);
        }
        $this->logger->info('Done');
    }

}



