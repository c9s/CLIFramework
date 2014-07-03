<?php
namespace TestApp\Command;
use CLIFramework\Command;
use Exception;

class ArginfoCommand extends Command
{

    public function arguments($args) {
        $args->add('name');
        $args->add('email');
        $args->add('phone')->optional();
    }

    public function execute($name, $email, $phone = null) { }
}




