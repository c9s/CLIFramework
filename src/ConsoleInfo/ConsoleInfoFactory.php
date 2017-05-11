<?php
namespace CLIFramework\ConsoleInfo;

use CLIFramework\ConsoleInfo\EnvConsoleInfo;
use CLIFramework\ConsoleInfo\TputConsoleInfo;

class ConsoleInfoFactory
{
    public static function create()
    {
        if (EnvConsoleInfo::hasSupport()) {
            return new EnvConsoleInfo;
        } elseif (TputConsoleInfo::hasSupport()) {
            return new TputConsoleInfo;
        }
    }
}
