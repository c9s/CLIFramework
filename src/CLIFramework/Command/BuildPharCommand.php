<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use CLIFramework\Autoload\ComposerAutoloadGenerator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RuntimeException;
use Exception;
use Phar;
use CodeGen\Expr\NewObjectExpr;
use CodeGen\Block;
use CodeGen\Statement\UseStatement;
use CodeGen\Statement\AssignStatement;
use CodeGen\Statement\MethodCallStatement;

/**
 * Build phar file from composer.json
 */
class BuildPharCommand extends Command
{
    public function brief()
    {
        return 'Build executable phar file from composer.json';
    }

    public function options($opts)
    {

        // append executable (bootstrap scripts, if it's not defined, it's just a library phar file.
        $opts->add('bootstrap?','bootstrap or executable php file')
            ;

        $opts->add('executable','make the phar file executable')
            ->isa('bool')
            ->defaultValue(true)
            ;

        $opts->add('c|compress?', 'compress type: gz, bz2')
            ->defaultValue('gz')
            ->validValues([ 'gz', 'bz2' ])
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
        $args->add('composer-config')->isa('file');
        $args->add('phar-file');
    }

    public function execute($composerConfigFile, $pharFile = 'output.phar')
    {
        if (!extension_loaded('json')) {
            throw new RuntimeException('json extension is required.');
        }

        $generator = new ComposerAutoloadGenerator;
        echo $generator->generate($composerConfigFile, $pharFile);

        ini_set('phar.readonly', 0);
        $this->logger->info("Creating phar file $pharFile...");

        $phar = new Phar($pharFile, 0, $pharFile);
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $phar->startBuffering();

        $stubs = [];

        if ($this->options->executable) {
            $this->logger->debug( 'Add shell bang...' );
            $stubs[] = "#!/usr/bin/env php";
        }
        // prepend open tag
        $stubs[] = '<?php';

        $this->logger->info("Setting up stub..." );
        $stubs[] = "Phar::mapPhar('$pharFile');";

        if ($bootstrap = $this->options->bootstrap) {
            $this->logger->info("Add $bootstrap");
            $content = php_strip_whitespace($bootstrap);
            $content = preg_replace('{^#!/usr/bin/env\s+php\s*}', '', $content);
            $phar->addFromString($bootstrap, $content);

            $this->logger->info( "Adding bootstrap script: $bootstrap" );
            $stubs[] = "require 'phar://$pharFile/$bootstrap';";
        }


        if ($adds = $this->options->add) {
            foreach ($adds as $add ) {
                $phar->buildFromIterator(
                    new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($add)),
                    getcwd()
                );
            }
        }

        $stubs[] = '__HALT_COMPILER();';
        $phar->setStub(join("\n",$stubs));


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
            $this->logger->info( "Compressing phar ..." );
            $phar->compressFiles($compressType);
        }

        $this->logger->info('Done');
    }

}



