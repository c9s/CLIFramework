<?php
namespace CLIFramework\ConsoleInfo;
use CLIFramework\ConsoleInfo\EnvConsoleInfo;
use CLIFramework\ConsoleInfo\TputConsoleInfo;

#[\AllowDynamicProperties]
class ConsoleInfoFactory
{

    static public function create()
    {
        if (EnvConsoleInfo::hasSupport()) {
            return new EnvConsoleInfo;
        } else if (TputConsoleInfo::hasSupport()) {
            return new TputConsoleInfo;
        }
    }


}



