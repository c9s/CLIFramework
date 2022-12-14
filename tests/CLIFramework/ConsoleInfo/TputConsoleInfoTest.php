<?php
use CLIFramework\ConsoleInfo\TputConsoleInfo;
use PHPUnit\Framework\TestCase;

/**
 * @group github_action
 */
class TputConsoleInfoTest extends TestCase
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

