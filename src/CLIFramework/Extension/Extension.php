<?php
namespace CLIFramework\Extension;

use CLIFramework\Command;

interface Extension
{
    public function bind(Command $command);
}
