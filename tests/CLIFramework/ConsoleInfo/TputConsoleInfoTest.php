<?php
use CLIFramework\ConsoleInfo\TputConsoleInfo;

class TputConsoleInfoTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        if (!TputConsoleInfo::hasSupport()) {
            skip('tput is not supported.');
        }
        $info = new TputConsoleInfo;
        ok($info);

        ok($info->getColumns());
        ok($info->getRows());
    }
}

