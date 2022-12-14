<?php
use CLIFramework\ConsoleInfo\EnvConsoleInfo;
use PHPUnit\Framework\TestCase;

class EnvConsoleInfoTest extends TestCase
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

