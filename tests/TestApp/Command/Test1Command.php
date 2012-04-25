<?php
namespace TestApp\Command;
use CLIFramework\Command;
use Exception;

class Test1Command extends Command
{

    function options($opts)
    {
        $opts->add('as:', 'as name');
    }

    function execute()
    {
        $args = func_get_args();
        if( empty($args) ) {
            throw new Exception('empty args');
        }

        if( ! $this->options->as ) {
            throw new Exception('empty --as=');
        }
    }


}




