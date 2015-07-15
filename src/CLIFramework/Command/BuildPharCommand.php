<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use CLIFramework\Autoload\ComposerAutoloadGenerator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RuntimeException;
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
        // $opts->add('composer', 'composer config file');
    }

    public function arguments($args)
    {
        $args->add('composer-config')->isa('file');
    }

    public function execute($composerConfigFile)
    {
        if (!extension_loaded('json')) {
            throw new RuntimeException('json extension is required.');
        }

        $generator = new ComposerAutoloadGenerator;
        echo $generator->generate($composerConfigFile);
    }

}



