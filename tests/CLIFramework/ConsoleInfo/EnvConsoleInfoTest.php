<?php
use CLIFramework\ConsoleInfo\EnvConsoleInfo;

class EnvConsoleInfoTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        if (!EnvConsoleInfo::hasSupport()) {
            skip('env console info is not supported.');
        }
        $info = new EnvConsoleInfo;
        ok($info);
        ok($info->getColumns());
        ok($info->getRows());
    }
}

