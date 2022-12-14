<?php
use CLIFramework\Logger;
use CLIFramework\Autoload\ComposerAutoloadGenerator;
use PHPUnit\Framework\TestCase;

class ComposerAutoloadGeneratorTest extends TestCase
{
    public function test()
    {
        $logger = new Logger;
        $logger->setQuiet();
        $workingDir = new SplFileInfo(getcwd());
        $vendorDirName = 'vendor';
        $autoloadGenerator = new ComposerAutoloadGenerator($logger);
        $autoloadGenerator->setVendorDir('vendor');
        $autoloadGenerator->setWorkingDir($workingDir->getPathname());
        $autoloadGenerator->scanComposerJsonFiles($workingDir . DIRECTORY_SEPARATOR . $vendorDirName);
    }
}

