<?php
namespace CLIFramework\Extension;
use CLIFramework\Command;
use CLIFramework\Extension\Extension;

interface CommandExtension extends Extension
{
    public function bindCommand(Command $command);
}
