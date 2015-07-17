<?php
use CLIFramework\Logger;
use CLIFramework\Autoload\ComposerAutoloadGenerator;

class ComposerAutoloadGeneratorTest extends PHPUnit_Framework_TestCase
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

