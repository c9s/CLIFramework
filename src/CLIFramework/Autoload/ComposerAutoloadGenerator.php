<?php
namespace CLIFramework\Autoload;
use RuntimeException;
use Exception;
use CodeGen\Expr\NewObjectExpr;
use CodeGen\Block;
use CodeGen\Statement\UseStatement;
use CodeGen\Statement\AssignStatement;
use CodeGen\Statement\MethodCallStatement;
use CodeGen\Statement\RequireStatement;
use Symfony\Component\Finder\Finder;
use Symfony\Component\ClassLoader\ClassMapGenerator;

class ComposerAutoloadGenerator
{
    /**
     * @var array[ package name ] = array
     */
    protected $packages = array();

    protected $workingDir = '';

    protected $vendorDir = 'vendor';

    public function __construct() 
    {
        $this->workingDir = getcwd(); // by default workingDir is current directory.
    }

    public function setWorkingDir($workingDir)
    {
        $this->workingDir = $workingDir;
    }

    public function setVendorDir($vendorDir)
    {
        $this->vendorDir = $vendorDir;
    }

    public function traceAutoloadsWithComposerJson($composerJson = 'composer.json', $includeRootDev = false)
    {
        $json = file_get_contents($composerJson);
        $obj = json_decode($json, true);
        return $this->traceAutoloads($obj, $includeRootDev);
    }

    public function traceAutoloadsWithRequirements(array $config, array $requirements = array())
    {
        $autoloads = array();
        foreach($requirements as $packageName => $requirement) {
            if (in_array($packageName, array('php', 'hhvm'))) {
                continue;
            } else if (preg_match('/^(?:ext|lib)-/',$packageName)) {
                continue;
            }


            // get config from composer.json
            $packageComposerJson = $this->workingDir . DIRECTORY_SEPARATOR . $this->vendorDir . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR . 'composer.json';
            if (file_exists($packageComposerJson)) {

                $packageAutoloads = $this->traceAutoloadsWithComposerJson($packageComposerJson, false); // don't include require-dev for dependencies
                $autoloads = array_merge($autoloads, $packageAutoloads);

            } else if (isset($this->packages[ $packageName ])) {

                $config = $this->packages[ $packageName ];
                $autoloads = $this->traceAutoloads($config, false);

            } else {
                // if (!file_exists($packageComposerJson)) {
                throw new RuntimeException("Missing composer.json file: $packageComposerJson");
            }
        }
        return $autoloads;
    }

    public function traceAutoloads(array $config, $includeRootDev = false )
    {
        $autoloads = array();

        // @see https://getcomposer.org/doc/02-libraries.md#platform-packages
        if (isset($config['require'])) {
            $autoloads = array_merge($autoloads, $this->traceAutoloadsWithRequirements($config, $config['require']));
        }
        if ($includeRootDev && isset($config['require-dev'])) {
            $autoloads = array_merge($autoloads, $this->traceAutoloadsWithRequirements($config, $config['require-dev']));
        }

        if (isset($config['autoload'])) {
            $baseDir = $this->vendorDir . DIRECTORY_SEPARATOR . $config['name'];

            // target-dir is deprecated, but somehow we need to support some
            // psr-0 class loader with target-dir
            // @see https://getcomposer.org/doc/04-schema.md#target-dir
            if (isset($config['target-dir'])) {
                $baseDir = $config['target-dir'];
                $autoloads[$config['name']] = $this->prependAutoloadPathPrefix($config['autoload'], $config['target-dir']);
            }
            
            if (isset($config['autoload']['classmap'])) {


                // Expand and replace classmap array
                $map = array();
                foreach ($config['autoload']['classmap'] as $path) {
                    $map = array_merge($map, ClassMapGenerator::createMap($this->workingDir . DIRECTORY_SEPARATOR . $baseDir . DIRECTORY_SEPARATOR . $path));
                }

                // Strip paths with working directory:
                // ClassMapGenerator returns class map with files in absolute paths,
                // We need them to be relative paths.
                foreach ($map as $k => $filepath) {
                    $map[$k] = str_replace( $this->workingDir . DIRECTORY_SEPARATOR . $baseDir . DIRECTORY_SEPARATOR, '', $filepath);
                }

                $config['autoload']['classmap'] = $map;

            }

            $autoloads[$config['name'] ] = $config['autoload'];
        }
        return $autoloads;
    }


    /**
     * Prepend a prefix for the structure in 'autoload' property. 
     * e.g., { 'psr-0': ... 'psr-4': ...  }
     */
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

    public function scanComposerJsonFiles()
    {
        // Find composer.json files that are not in their corresponding package directory
        $finder = new Finder();
        $finder->name('composer.json');
        $finder->in($this->workingDir . DIRECTORY_SEPARATOR . $this->vendorDir);
        foreach ($finder as $file) {
            $config = json_decode(file_get_contents($file), true);
            $this->packages[ $config['name'] ] = $config;
        }
    }

    public function generate($composerConfigFile, $pharFile = 'output.phar')
    {
        $pharMap = 'phar://' . $pharFile . '/';
        $autoloads = $this->traceAutoloadsWithComposerJson($composerConfigFile, $this->vendorDir, true);

        $psr0 = array();
        $psr4 = array();
        $files = array();
        $map = array();

        foreach($autoloads as $packageName => $autoload) {

            // The returned autoload paths are relative paths in their packages
            // We need to prepend the package base dir path
            $autoload = $this->prependAutoloadPathPrefix($autoload, $pharMap . $this->vendorDir . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR);

            if (isset($autoload['psr-4'])) {

                $psr4 = array_merge($psr4, $autoload['psr-4']);

            }
            if (isset($autoload['psr-0'])) {

                $psr0 = array_merge($psr0, $autoload['psr-0']);

            }
            if (isset($autoload['files'])) {

                $files = array_merge($files, $autoload['files']);

            }
            if (isset($autoload['classmap'])) {

                // the classmap here is an expanded classmap associative array
                $map = array_merge($map, $autoload['classmap']);

            }
        }


        // Generate classloader initialization code
        $block   = new Block;
        $block[] = new UseStatement('Universal\\ClassLoader\\Psr0ClassLoader');
        $block[] = new UseStatement('Universal\\ClassLoader\\Psr4ClassLoader');
        $block[] = new UseStatement('Universal\\ClassLoader\\MapClassLoader');

        if (!empty($files)) {
            foreach ($files as $file) {
                $block[] = new RequireStatement($file);
            }
        }

        if (!empty($map)) {
            $block[] = new AssignStatement('$map', new NewObjectExpr('MapClassLoader', [$map]));
            $block[] = new MethodCallStatement('$map','register',[ false ]);
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





