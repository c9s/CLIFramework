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
     * translate command name to class name
     *
     * list => ListCommand
     * list-all => ListAllCommand
     **/
    public function translate($command)
    {
        $args = explode('-',$command);
        foreach($args as & $a)
            $a = ucfirst($a);

        return join('',$args) . 'Command';
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

    /*
     * load command class/subclass
     */
    public function loadClass($class)
    {
        if( class_exists($class ))

            return $class;

        // if it's a full-qualified class name.
        if ($class[0] == '\\') {
            spl_autoload_call( $class );
            if( class_exists($class) )

                return $class;
            else
                throw new Exception("Command class $class not found.");
        } else {
            // for subcommand class name (under any subcommand namespace)
            // has application command class ?
            foreach ($this->namespaces as $ns) {
                $fullclass = $ns . '\\' . $class;

                # echo "\nLooking for $fullclass\n";
                if( class_exists($fullclass) )

                    return $fullclass;

                spl_autoload_call( $fullclass );
                if( class_exists($fullclass) )

                    return $fullclass;
            }
        }
    }



    /*
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

}
