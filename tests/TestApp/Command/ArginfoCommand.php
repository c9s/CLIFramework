<?php
namespace TestApp\Command;
use CLIFramework\Command;
use Exception;

class ArginfoCommand extends Command
{

    public function arginfo() {
        $this->arg('name');
        $this->arg('email');
        $this->arg('phone')->optional();
    }

    public function execute($name, $email, $phone = null) { }
}




