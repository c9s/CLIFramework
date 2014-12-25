<?php
/*
 * This file is part of the CLIFramework package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace CLIFramework;
use CLIFramework\Exception\CommandClassNotFoundException;
use Exception;

class CommandLoader
{
    public $namespaces = array();

    public function addNamespace( $ns )
    {
        $nss = (array) $ns;
        foreach ($nss as $n) {
            $this->namespaces[] = $n;
        }
    }

    /**
     * Translate command name to class name.
     *
     * This method convert "foo-bar" to "FooBar", so if you have command name like "foo-bar",
     * this method returns FooBar class. e.g.,
     *
     *    list => ListCommand
     *    list-all => ListAllCommand
     *
     * @param string $command command name.
     * @return string class name.
     */
    public function translate($command)
    {
        $args = explode('-',$command);
        foreach($args as & $a) {
            $a = ucfirst($a);
        }
        return join('',$args) . 'Command';
    }

    /**
     * Translate class name to command name
     *
     * This method is inverse of self::translate()
     *
     *     HelpCommand => help
     *     SuchALongCommand => such-a-long
     *
     * @param string $className class name.
     * @return string translated command name.
     */
    public function inverseTranslate($className)
    {
        if (substr($className, -7) !== 'Command')
            throw new \InvalidArgumentException("Command class name need to end with 'Command'");
        // remove the suffix 'Command', then lower case the first letter
        $className = lcfirst(substr($className, 0, -7));
        return preg_replace_callback(
            '/[A-Z]/',
            function ($matches) { return '-' . strtolower($matches[0]); },
            $className
        );
    }

    /**
     * load command class:
     *
     * @param  string  $command command name
     * @return boolean
     **/
    public function load($command)
    {
        $subclass = $this->translate($command);
        return $this->loadClass( $subclass );
    }

    /**
     * Load command class/subclass
     *
     * @param string $class
     * @return string loaded class name
     */
    public function loadClass($class)
    {
        if (class_exists($class)) {
            return $class;
        }

        // for subcommand class name (under any subcommand namespace)
        // has application command class ?
        foreach ($this->namespaces as $ns) {
            $fullclass = $ns . '\\' . $class;
            if (class_exists($fullclass)) {
                return $fullclass;
            }
        }
        throw new CommandClassNotFoundException($class, $this->namespaces);
    }


    /**
     * load subcommand class from command name
     *
     * @param $command
     * @param $parent parent command class
     *
     * */
    public function loadSubcommand($subcommand, $parent)
    {
        $parent_class = get_class($parent);
        $class = '\\' . $parent_class . '\\' . $this->translate($subcommand);
        return $this->loadClass($class);
    }

    static public function getInstance() {
        static $instance;
        if ( $instance ) {
            return $instance;
        }
        return $instance = new self;
    }

}
