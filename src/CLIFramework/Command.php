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
    /**
     * @var CLIFramework\Application Application object.
     */
    public $application;


    /**
     * @var string Command alias string.
     */
    public $alias;

    public function __construct($application = null)
    {
        // this variable is optional (for backward compatibility)
        if( $application ) {
            $this->application = $application;
        }
        parent::__construct();
    }

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


    /**
     * Returns logger object.
     *
     * @return CLIFramework\Logger
     */
    public function getLogger()
    {
        if( $app = $this->application ) {
            return $app::getLogger();
        }
    }


    /**
     * Alias setter
     *
     * @param string $alias
     */
    public function alias($alias)
    {
        $this->alias = $alias;
        return $this;
    }


    /**
     * Provide a shorthand property for retrieving logger object.
     *
     * @param string $k property name
     */
    public function __get($k)
    {
        if( $k === 'logger' ) {
            return $this->getLogger();
        }
        throw new Exception( "$k is not defined." );
    }

}

