<?php

/**
 * This file is part of the CLIFramework package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CLIFramework;
use GetOptionKit\GetOptionKit;

/**
 * abstract command class
 *
 */
abstract class Command extends CommandBase
{
    public $application;


    /**
     * translate current class name to command name.
     *
     * @return string command name
     */
    public function getCommandName()
    {
        $class = get_class($this);
        $class = preg_replace( '/Command$/','', $class );
        $parts = explode('\\',$class);
        $class = end($parts);
        return strtolower( preg_replace( '/(?<=[a-z])([A-Z])/', '-\1' , $class ) );
    }

    public function getLogger()
    {
        $app = $this->application;
        return $app::getLogger();
    }

    public function __get($k)
    {
        if( $k === 'logger' ) {
            return $this->getLogger();
        }
        throw new Exception( "$k is not defined." );
    }

}

