<?php
use PHPUnit\Framework\TestCase;

use CLIFramework\ConsoleInfo\TputConsoleInfo;

class TputConsoleInfoTest extends TestCase
{
    public function test()
    {
        if (!TputConsoleInfo::hasSupport()) {
            return $this->markTestSkipped('tput is not supported.');
        }
        $info = new TputConsoleInfo;
        $this->assertNotNull($info->getColumns());
        $this->assertNotNull($info->getRows());
    }
}

