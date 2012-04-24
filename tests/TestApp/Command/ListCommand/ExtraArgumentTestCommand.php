<?php
namespace TestApp\Command\ListCommand;
use Exception;

class ExtraArgumentTestCommand extends \CLIFramework\Command
{

    function options($opts)
    {
        $opts->add('as:','required a value');
    }

    function execute()
    {
        if( null === $this->options->as )
            throw new Exception( '--as option is required.' );
        $args = func_get_args();
        if( empty($args) )
            throw new Exception( 'command argument is required' );
    }

}


