<?php
namespace CLIFramework\Command;

use CLIFramework\Command;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RuntimeException;
use Phar;

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

    public function traceAutoloadsWithComposerJson($composerJson = 'composer.json', $includeRootDev = false)
    {
        $json = file_get_contents($composerJson);
        $obj = json_decode($json, true);
        return $this->traceAutoloads($obj, $includeRootDev);
    }

    public function traceAutoloadsWithRequirements(array $requirements = array(), $vendorDir = 'vendor')
    {
        $autoloads = array();
        foreach($requirements as $packageName => $requirement) {
            if (in_array($packageName, array('php', 'hhvm'))) {
                continue;
            } else if (preg_match('/^(?:ext|lib)-/',$packageName)) {
                continue;
            }

            $packageComposerJson = $vendorDir . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR . 'composer.json';
            if (!file_exists($packageComposerJson)) {
                throw new RuntimeException("Missing composer.json file: $packageComposerJson");
            }
            $packageAutoloads = $this->traceAutoloadsWithComposerJson($packageComposerJson, false); // don't include require-dev for dependencies
            $autoloads = array_merge($autoloads, $packageAutoloads);
        }
        return $autoloads;
    }

    public function traceAutoloads(array $config, $includeRootDev = false, $vendorDir = 'vendor')
    {
        $autoloads = array();

        if (isset($config['require'])) {
            $autoloads = array_merge($autoloads, $this->traceAutoloadsWithRequirements($config['require']));
        }
        if ($includeRootDev && isset($config['require-dev'])) {
            $autoloads = array_merge($autoloads, $this->traceAutoloadsWithRequirements($config['require-dev']));
        }

        if (isset($config['autoload'])) {
            $autoloads[ $config['name'] ] = $config['autoload'];
        }
        return $autoloads;
    }

    public function execute($composerConfigFile)
    {
        if (!extension_loaded('json')) {
            throw new RuntimeException('json extension is required.');
        }

        $autoloads = $this->traceAutoloadsWithComposerJson($composerConfigFile);
        foreach($autoloads as $packageName => $autoload) {
            var_dump( $autoload );
            if (isset($autoload['psr-4'])) {

            } else if (isset($autoload['prs-0'])) {

            } else if (isset($autoload['files'])) {

            }
        }
    }

}



