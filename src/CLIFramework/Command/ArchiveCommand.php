<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use CLIFramework\Autoload\ComposerAutoloadGenerator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
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
use CLIFramework\PharKit\PharURI;
use CLIFramework\Utils;
use ReflectionClass;
use ReflectionObject;
use SplFileInfo;

/**
 * Archive: build phar file from composer.json
 *
 * Debug commands:
 *
 *    php example/demo --debug archive --no-compress --composer ../AssetKit/composer.json --bootstrap ../AssetKit/scripts/assetkit.php app.phar && php app.phar
 *
 *    php example/demo --debug archive
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
            ->multiple()
            ->isa('file')
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

        $opts->add('no-classloader','do not embed a built-in classloader in the generated phar file.')
            ;

        $opts->add('app-bootstrap', 'Include CLIFramework bootstrap script.');

        /*
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
        return array('a', 'ar');
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

        $pharGenerator = new PharGenerator($this->logger, $this->options, $pharFile);
        $phar = $pharGenerator->getPhar();
        ini_set('phar.readonly', 0);
        $this->logger->info("Creating phar file $pharFile...");

        $phar->startBuffering();

        $stubs = new Block;
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
            $stubs[] = new RequireStatement(new PharURI($pharFile, basename($classPath)));
        }

        if (!$this->options->{'no-classloader'}) {
            $this->logger->info('Generating classLoader stubs');
            $autoloadGenerator = new ComposerAutoloadGenerator($this->logger);
            $autoloadGenerator->setVendorDir('vendor');
            $autoloadGenerator->setWorkingDir($workingDir->getPathname());
            $autoloadGenerator->scanComposerJsonFiles($workingDir . DIRECTORY_SEPARATOR . $vendorDirName);

            $autoloads = $autoloadGenerator->traceAutoloadsWithComposerJson($composerConfigFile, $workingDir . DIRECTORY_SEPARATOR . $vendorDirName, true);
            foreach($autoloads as $packageName => $config) {
                if (!isset($config['autoload'])) {
                    continue;
                }

                $autoload = $config['autoload'];

                if (!isset($config['root'])) {
                    $autoload = $autoloadGenerator->prependAutoloadPathPrefix($autoload, $vendorDirName . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR);
                }

                foreach ($autoload as $type => $map) {
                    foreach ($map as $mapPaths) {
                        $paths = (array) $mapPaths;
                        foreach ($paths as $path) {
                            $absolutePath = $workingDir . DIRECTORY_SEPARATOR . $path;

                            if (is_dir($absolutePath)) {
                                $this->logger->debug("Add files from directory $absolutePath under $workingDir");

                                $it = new RecursiveIteratorIterator(
                                    new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS)
                                );

                                foreach ($it as $fileinfo) {
                                    $pathName = $fileinfo->getPathname();
                                    if (preg_match('/(\.(?:git|svn|hg)|Tests|Test\.php)/', $pathName)) {
                                        continue;
                                    }
                                    $localPath = str_replace($workingDir . DIRECTORY_SEPARATOR, "", $pathName);
                                    $this->logger->debug("Adding $localPath");
                                    $phar->addFile($pathName, $localPath);
                                }

                                /*
                                $builtFiles = $phar->buildFromIterator(
                                    new RecursiveIteratorIterator(
                                        new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS)
                                    ),
                                    $workingDir
                                );
                                */
                            } else if (is_file($absolutePath)) {
                                $this->logger->debug("Add file $absolutePath under $path");
                                $phar->addFile($absolutePath, $path);
                            } else {
                                $this->logger->error("File '$absolutePath' is not found.");
                            }

                        }
                    }
                }
            }
            $classloaderStub = $autoloadGenerator->generate($composerConfigFile, $pharFile);
            $this->logger->debug("ClassLoader stub:");
            $this->logger->debug($classloaderStub);
            $stubs[] = $autoloadGenerator->generate($composerConfigFile, $pharFile);
        }

        if ($bootstraps = $this->options->bootstrap) {
            foreach ($bootstraps as $bootstrap) {
                $this->logger->info("Adding bootstrap: $bootstrap");
                $content = php_strip_whitespace($bootstrap);
                $content = preg_replace('{^#!/usr/bin/env\s+php\s*}', '', $content);

                $localPath = str_replace($workingDir->getPathname(), '', $bootstrap->getRealPath());

                $phar->addFromString($localPath, $content);

                $stubs[] = new RequireStatement(new PharURI($pharFile, $localPath));
            }
        }

        if ($this->options->{'app-bootstrap'}) {
            $app = $this->getApplication();
            $refObject = new ReflectionObject($app);
            $appClassName = $refObject->getName();

            $block = new Block;
            $block[] = new AssignStatement('$app', new NewObjectExpr($appClassName));
            $block[] = new MethodCallStatement('$app', 'run', array('$argv'));
            $stubs[] = $block;
        }


        $stubs[] = '__HALT_COMPILER();';

        $stubstr = $stubs->render();
        $this->logger->debug($stubstr);
        $phar->setStub($stubstr);


        // Add some extra files in phar's root
        if ($adds = $this->options->add) {
            foreach ($adds as $add) {
                $phar->buildFromIterator(
                    new RecursiveIteratorIterator(new RecursiveDirectoryIterator($add)),
                    $workingDir
                );
            }
        }

        $pharGenerator->generate();


        $this->logger->info('Done');
    }

}



