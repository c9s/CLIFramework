<?php
use PHPUnit\Framework\TestCase;

use CLIFramework\ConsoleInfo\EnvConsoleInfo;

class EnvConsoleInfoTest extends TestCase
{
    public function test()
    {
        if (!EnvConsoleInfo::hasSupport()) {
            return $this->markTestSkipped('env console info is not supported.');
        }
        $info = new EnvConsoleInfo;
        $this->assertNotNull($info->getColumns());
        $this->assertNotNull($info->getRows());
    }
}

