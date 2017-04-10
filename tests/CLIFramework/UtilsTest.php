<?php
use PHPUnit\Framework\TestCase;

use CLIFramework\Utils;

class UtilsTest extends TestCase
{
    public function testGetClassPath()
    {
        $path = Utils::getClassPath('Universal\\ClassLoader\\ClassLoader', getcwd());
        $this->assertEquals('vendor/corneltek/universal/src/Universal/ClassLoader/ClassLoader.php', $path);
        $this->assertFileExists($path);
    }
}

