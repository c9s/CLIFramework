<?php
namespace CLIFramework;
use CLIFramework\Command;



/**
 * A ChainedCommand contains multiple commands together,
 *
 * Subcommands of a chained command use the same options,
 * same logger, same application and are executed by sequence.
 */
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
            $cmd->parent = $this;
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

