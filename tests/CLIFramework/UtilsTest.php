<?php
use PHPUnit\Framework\TestCase;

use CLIFramework\Utils;

class UtilsTest extends TestCase
{
    public function testGetClassPath()
    {
        $path = Utils::getClassPath('Universal\\ClassLoader\\ClassLoader', getcwd());
        $this->assertEquals('vendor/universal/universal/src/ClassLoader/ClassLoader.php', $path);
        $this->assertFileExists($path);
    }
}

