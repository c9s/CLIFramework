<?php
namespace CLIFramework\Autoload;
use RuntimeException;
use CodeGen\Expr\NewObjectExpr;
use CodeGen\Block;
use CodeGen\Statement\UseStatement;
use CodeGen\Statement\AssignStatement;
use CodeGen\Statement\MethodCallStatement;

class ComposerAutoloadGenerator
{

    public function traceAutoloadsWithComposerJson($composerJson = 'composer.json', $vendorDir = 'vendor', $includeRootDev = false)
    {
        $json = file_get_contents($composerJson);
        $obj = json_decode($json, true);
        return $this->traceAutoloads($obj, $vendorDir, $includeRootDev);
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
            $packageAutoloads = $this->traceAutoloadsWithComposerJson($packageComposerJson, $vendorDir, false); // don't include require-dev for dependencies
            $autoloads = array_merge($autoloads, $packageAutoloads);
        }
        return $autoloads;
    }

    public function traceAutoloads(array $config, $vendorDir = 'vendor', $includeRootDev = false )
    {
        $autoloads = array();

        // @see https://getcomposer.org/doc/02-libraries.md#platform-packages
        if (isset($config['require'])) {
            $autoloads = array_merge($autoloads, $this->traceAutoloadsWithRequirements($config['require']));
        }
        if ($includeRootDev && isset($config['require-dev'])) {
            $autoloads = array_merge($autoloads, $this->traceAutoloadsWithRequirements($config['require-dev']));
        }

        if (isset($config['autoload'])) {
            // Rewrite autoload path with vendor base dir
            $autoloads[ $config['name'] ] = $config['autoload'];
        }
        return $autoloads;
    }

    public function prependAutoloadPathPrefix($autoloads, $prefix)
    {
        $newAutoloads = array();
        foreach ($autoloads as $autoloadType => & $autoloadConfig) {
            $newConfig = array();
            foreach ($autoloadConfig as $ns => $path) {
                if (is_array($path)) {
                    $newConfig[ $ns ] = array_map($path, function($p) use ($prefix) {
                        return $prefix . $p;
                    });

                } else {
                    $newConfig[ $ns ] = $prefix . $path;
                }
            }
            $newAutoloads[ $autoloadType ] = $newConfig;
        }
        return $newAutoloads;
    }


    public function generate($composerConfigFile)
    {
        $pharFileName = 'myapp.phar';
        $pharMap = 'phar://' . $pharFileName . '/';
        $vendorDir = 'vendor';
        $autoloads = $this->traceAutoloadsWithComposerJson($composerConfigFile, $vendorDir, true);

        $block   = new Block;
        $block[] = new UseStatement('Universal\\ClassLoader\\Psr0ClassLoader');
        $block[] = new UseStatement('Universal\\ClassLoader\\Psr4ClassLoader');

        $psr0 = array();
        $psr4 = array();
        $files = array();
        foreach($autoloads as $packageName => $autoload) {
            $autoload = $this->prependAutoloadPathPrefix($autoload, $pharMap . $vendorDir . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR);
            if (isset($autoload['psr-4'])) {
                $psr4 = array_merge($psr4, $autoload['psr-4']);
            } else if (isset($autoload['prs-0'])) {
                $psr0 = array_merge($psr0, $autoload['psr-0']);
            } else if (isset($autoload['files'])) {
                $files = array_merge($autoload['files']);
            }
        }


        if (!empty($psr4)) {
            $block[] = new AssignStatement('$psr4', new NewObjectExpr('Psr4ClassLoader', [$psr4]));
            $block[] = new MethodCallStatement('$psr4','register',[ false ]);
        }

        if (!empty($psr0)) {
            $block[] = new AssignStatement('$psr0', new NewObjectExpr('Psr0ClassLoader', [$psr0]));
            $block[] = new MethodCallStatement('$psr0','register',[ false ]);
        }
        return $block->render([]);
    }

}





