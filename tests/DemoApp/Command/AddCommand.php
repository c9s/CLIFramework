<?php
namespace DemoApp\Command;
use CLIFramework\Command;

class AddCommand extends Command {

    public function brief() { return 'Add a file'; }


    public function options($opts) {
        $opts->add('f|force', 'Allow adding otherwise ignored files.');
        $opts->add('i|interactive', 'Add modified contents in the working tree interactively to the index.' 
            . 'Optional path arguments may be supplied to limit operation to a subset of the working tree. See "Interactive mode" for details.');
    }

    public function arguments($args) 
    {
        # XXX: Add a DSL here to support zsh/bash function completion 
        $args->add('file');
    }


    public function execute() {
        $this->getLogger()->info('executing add command.');
    }
}

