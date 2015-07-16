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
        $opts->add('d|working-dir:', 'If specified, use the given directory as working directory.');

        $opts->add('c|composer:', 'The composer.json file. If --working-dir is ignored, dirname of the composer.json will be used.')
            ->defaultValue('composer.json')
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

    public function execute($pharFile = 'output.phar')
    {
        if (!extension_loaded('json')) {
            throw new RuntimeException('json extension is required.');
        }

        $composerConfigFile = $this->options->{'composer'} ?: 'composer.json';

        if (!file_exists($composerConfigFile)) {
            throw new Exception("$composerConfigFile doesn't exist.");
        }

        $workingDir = $this->options->{'working-dir'} ?: getcwd();
        if (!file_exists($workingDir)) {
            throw new Exception("$workingDir doesn't exist.");
        }





        $vendorDir = 'vendor';

        $pharGenerator = new PharGenerator($this->logger, $pharFile);
        $phar = $pharGenerator->getPhar();
        ini_set('phar.readonly', 0);
        $this->logger->info("Creating phar file $pharFile...");

        $phar->startBuffering();

        $stubs = array();
        if ($this->options->executable) {
            $this->logger->debug( 'Add shell bang...' );
            $stubs[] = "#!/usr/bin/env php";
        }
        // prepend open tag
        $stubs[] = '<?php';

        $this->logger->info("Setting up stub..." );
        $stubs[] = "Phar::mapPhar('$pharFile');";

        // $workingDir = dirname(realpath($composerConfigFile));

        $requires = array(
            Utils::getClassPath('Universal\\ClassLoader\\ClassLoader', $workingDir),
            Utils::getClassPath('Universal\\ClassLoader\\Psr0ClassLoader', $workingDir),
            Utils::getClassPath('Universal\\ClassLoader\\Psr4ClassLoader', $workingDir),
            Utils::getClassPath('Universal\\ClassLoader\\MapClassLoader', $workingDir),
        );

        // Generate class loader stub
        $fileinfo = new SplFileInfo($requires[0]);

        // $phar->buildFromDirectory($fileinfo->getPath());

        $phar->buildFromIterator(
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fileinfo->getPath())),
            $workingDir
        );

        foreach ($requires as $requirefile) {
            $stubs[] = "require 'phar://$pharFile/$requirefile';";
        }

        if ($bootstrap = $this->options->bootstrap) {
            $this->logger->info("Add $bootstrap");
            $content = php_strip_whitespace($bootstrap);
            $content = preg_replace('{^#!/usr/bin/env\s+php\s*}', '', $content);
            $phar->addFromString($bootstrap, $content);

            $this->logger->info( "Adding bootstrap script: $bootstrap" );
            $stubs[] = "require 'phar://$pharFile/$bootstrap';";
        }

        $this->logger->info('Generating classLoader stubs');
        $generator = new ComposerAutoloadGenerator;
        $generator->scanComposerJsonFiles($workingDir . DIRECTORY_SEPARATOR . $vendorDir);

        $autoloads = $generator->traceAutoloadsWithComposerJson($composerConfigFile, $vendorDir, true);
        foreach($autoloads as $packageName => $autoload) {
            $autoload = $generator->prependAutoloadPathPrefix($autoload, $vendorDir . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR);
            foreach ($autoload as $type => $map) {
                foreach ($map as $mapPaths) {
                    $paths = (array) $mapPaths;
                    foreach ($paths as $path) {
                        if (is_dir($path)) {
                            $phar->buildFromIterator(
                                new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)),
                                $workingDir
                            );
                        } else if (is_file($path)) {
                            $phar->addFile($path, $path);
                        }

                    }
                }
            }
        }


        echo $generator->generate($composerConfigFile, $pharFile, $vendorDir);

        $stubs[] = $generator->generate($composerConfigFile, $pharFile, $vendorDir);


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



