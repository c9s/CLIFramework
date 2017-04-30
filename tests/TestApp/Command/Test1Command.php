<?php
namespace TestApp\Command;
use CLIFramework\Command;
use Exception;

class Test1Command extends Command
{

    public function options($opts)
    {
        $opts->add('as:', 'as name');
    }

    public function execute()
    {
        $args = func_get_args();
        if (empty($args)) {
            throw new Exception('empty args');
        }
        if (! $this->options->as) {
            throw new Exception('The value of option --as is empty.');
        }
        return $this->options->as;
    }


}




