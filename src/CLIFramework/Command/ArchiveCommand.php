<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use CLIFramework\Autoload\ComposerAutoloadGenerator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RuntimeException;
use Exception;
use Phar;
use CodeGen\Block;
use CodeGen\Expr\NewObjectExpr;
use CodeGen\Statement\UseStatement;
use CodeGen\Statement\FunctionCallStatement;
use CodeGen\Statement\AssignStatement;
use CodeGen\Statement\MethodCallStatement;
use CodeGen\Statement\RequireStatement;
use CLIFramework\PharKit\PharGenerator;
use CLIFramework\Utils;
use ReflectionClass;
use SplFileInfo;

/**
 * Archive: build phar file from composer.json
 */
class ArchiveCommand extends Command
{
    public function brief()
    {
        return 'Build executable phar file from composer.json';
    }

    public function options($opts)
    {
        $opts->add('d|working-dir:', 'If specified, use the given directory as working directory.')
            ->isa('dir')
            ;

        $opts->add('c|composer:', 'The composer.json file. If --working-dir is ignored, dirname of the composer.json will be used.')
            ->isa('file')
            ->defaultValue('composer.json')
            ;
        
        $opts->add('vendor:', 'Vendor directory name')
            ->defaultValue('vendor')
            ;

        // append executable (bootstrap scripts, if it's not defined, it's just a library phar file.
        $opts->add('bootstrap?','bootstrap or executable php file')
            ;

        $opts->add('executable','make the phar file executable')
            ->isa('bool')
            ->defaultValue(true)
            ;

        $opts->add('c|compress?', 'compress type: gz, bz2')
            ->defaultValue('gz')
            ->validValues(array( 'gz', 'bz2'))
            ;

        $opts->add('no-compress', 'do not compress phar file.');

        $opts->add('add+', 'add a path respectively');

        $opts->add('exclude+' , 'exclude pattern');
        /*

        // optional classloader script (use Universal ClassLoader by default 
        $opts->add('classloader?','embed a classloader in phar file');


        $opts->add('lib+','library path');



        $opts->add('output:','output');
         */
    }

    public function arguments($args)
    {
        $args->add('phar-file');
    }


    public function aliases()
    {
        return [ 'a', 'ar' ];
    }


    public function execute($pharFile = 'output.phar')
    {
        if (!extension_loaded('json')) {
            throw new RuntimeException('json extension is required.');
        }

        // $composerConfigFile is a SplFileInfo object since wuse ->isa('file')
        $composerConfigFile = $this->options->{'composer'} ?: 'composer.json';
        if (!file_exists($composerConfigFile)) {
            throw new Exception("composer config '$composerConfigFile' doesn't exist.");
        }
        $composerConfigFile = new SplFileInfo(realpath($composerConfigFile));
        $this->logger->debug("Found composer config at $composerConfigFile");


        // workingDir is a SplFileInfo object since we use ->isa('Dir')
        $workingDir = $this->options->{'working-dir'} ?: new SplFileInfo($composerConfigFile->getPath());
        if (!file_exists($workingDir)) {
            throw new Exception("working directory '$workingDir' doesn't exist.");
        }
        $this->logger->debug("Working directory: " . $workingDir->getPathname());

        $vendorDirName = $this->options->vendor ?: 'vendor';

        $pharGenerator = new PharGenerator($this->logger, $pharFile);
        $phar = $pharGenerator->getPhar();
        ini_set('phar.readonly', 0);
        $this->logger->info("Creating phar file $pharFile...");

        $phar->startBuffering();

        $stubs = array();
        if ($this->options->executable) {
            $this->logger->debug( 'Adding shell bang...' );
            $stubs[] = "#!/usr/bin/env php";
        }
        // prepend open tag
        $stubs[] = '<?php';

        $this->logger->info("Setting up stub..." );
        $stubs[] = "Phar::mapPhar('$pharFile');";

        // $workingDir = dirname(realpath($composerConfigFile));


        // Get class paths by ReflectionClass, they should be relative path.
        // However the class path might be in another directory because the
        // classes are loaded from vendor/autoload.php
        $classPaths = array(
            Utils::getClassPath('Universal\\ClassLoader\\ClassLoader'),
            Utils::getClassPath('Universal\\ClassLoader\\Psr0ClassLoader'),
            Utils::getClassPath('Universal\\ClassLoader\\Psr4ClassLoader'),
            Utils::getClassPath('Universal\\ClassLoader\\MapClassLoader'),
        );

        // Generate class loader stub
        $this->logger->debug("Adding class loader files...");
        foreach ($classPaths as $classPath) {
            $phar->addFile($classPath, basename($classPath));
        }

        /*
        $classDir = dirname($classPaths[0]);
        $phar->buildFromIterator(
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator($classDir)),
            $workingDir
        );
         */
        foreach ($classPaths as $classPath) {
            $this->logger->debug("Adding require statment for class loader: " . basename($classPath));
            $stmt = new RequireStatement("phar://$pharFile/" . basename($classPath));
            $stubs[] = $stmt->render();
        }


        if ($bootstrap = $this->options->bootstrap) {
            $this->logger->info("Adding bootstrap: $bootstrap");
            $content = php_strip_whitespace($bootstrap);
            $content = preg_replace('{^#!/usr/bin/env\s+php\s*}', '', $content);
            $phar->addFromString($bootstrap, $content);

            $stmt = new RequireStatement("phar://$pharFile/" . $bootstrap);
            $stubs[] = $stmt->render();
        }

        $this->logger->info('Generating classLoader stubs');
        $generator = new ComposerAutoloadGenerator;
        $generator->setVendorDir('vendor');
        $generator->setWorkingDir($workingDir->getPathname());
        $generator->scanComposerJsonFiles($workingDir . DIRECTORY_SEPARATOR . $vendorDirName);

        $autoloads = $generator->traceAutoloadsWithComposerJson($composerConfigFile, $workingDir . DIRECTORY_SEPARATOR . $vendorDirName, true);
        foreach($autoloads as $packageName => $autoload) {
            $autoload = $generator->prependAutoloadPathPrefix($autoload, $vendorDirName . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR);
            foreach ($autoload as $type => $map) {
                foreach ($map as $mapPaths) {
                    $paths = (array) $mapPaths;
                    foreach ($paths as $path) {
                        $absolutePath = $workingDir . DIRECTORY_SEPARATOR . $path;

                        if (is_dir($absolutePath)) {
                            $phar->buildFromIterator(
                                new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolutePath)),
                                $workingDir
                            );
                        } else if (is_file($absolutePath)) {
                            $phar->addFile($absolutePath, $path);
                        } else {
                            $this->logger->error("File '$absolutePath' is not found.");
                        }

                    }
                }
            }
        }

        $classloaderStub = $generator->generate($composerConfigFile, $pharFile);
        $this->logger->debug($classloaderStub);

        $stubs[] = $generator->generate($composerConfigFile, $pharFile);

        $stubs[] = '__HALT_COMPILER();';
        $phar->setStub(join("\n",$stubs));


        // Include files in phar's root
        if ($adds = $this->options->add) {
            foreach ($adds as $add) {
                $phar->buildFromIterator(
                    new RecursiveIteratorIterator(new RecursiveDirectoryIterator($add)),
                    getcwd()
                );
            }
        }






        // Finish building...
        $phar->stopBuffering();

        $compressType = Phar::GZ;
        if ($this->options->{'no-compress'} ) {
            $compressType = null;
        } else if ($type = $this->options->compress) {
            switch ($type) {
            case 'gz':
                $compressType = Phar::GZ;
                break;
            case 'bz2':
                $compressType = Phar::BZ2;
                break;
            default:
                throw new Exception("Phar compression: $type is not supported, valid values are gz, bz2");
                break;
            }
        }
        if ($compressType) {
            $this->logger->info( "Compressing phar files...");
            // $phar = $phar->compress($compressType);
            $phar->compressFiles($compressType);
        }

        $this->logger->info('Done');


    }

}



