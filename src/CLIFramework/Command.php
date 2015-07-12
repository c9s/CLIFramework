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
use Exception;
use CLIFramework\CommandInterface;
use CLIFramework\Exception\CommandClassNotFoundException;
use CLIFramework\Application;

/**
 * abstract command class
 *
 */
abstract class Command extends CommandBase
    implements CommandInterface
{
    /**
     * @var CLIFramework\Application Application object.
     */
    public $application;

    public $name;

    public function __construct(CommandBase $parent = null)
    {
        parent::__construct($parent);
    }

    public function setApplication(Application $application)
    {
        $this->application = $application;
    }


    /**
     * Get the main application object from parents
     *
     * @return Application
     */
    public function getApplication() {
        if ($this->application) {
            return $this->application;
        }
        $p = $this->parent;
        while (true) {
            if ( ! $p ) {
                return null;
            }
            if ($p instanceof Application) {
                return $p;
            }
            $p = $p->parent;
        }
    }

    public function hasApplication() {
        return $this->getApplication() !== null;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Translate current class name to command name.
     *
     * @return string command name
     */
    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }

        // Extract command name from the class name.
        $class = get_class($this);
        // strip command suffix
        $parts = explode('\\',$class);
        $class = end($parts);
        $class = preg_replace( '/Command$/','', $class );
        return strtolower( preg_replace( '/(?<=[a-z])([A-Z])/', '-\1' , $class ) );
    }


    /**
     * Returns logger object.
     *
     * @return CLIFramework\Logger
     */
    public function getLogger()
    {
        return $this->getApplication()->getLogger();
    }



    /**
     * Returns text style formatter.
     *
     * @return CLIFramework\Formatter
     */
    public function getFormatter()
    {
        return $this->getApplication()->getFormatter();
    }

    /**
     * User may register their aliases
     */
    public function aliases() {
        return array();
    }



    /**
     * Provide a shorthand property for retrieving logger object.
     *
     * @param string $k property name
     */
    public function __get($k)
    {
        if ($k === 'logger') {
            return $this->getLogger();
        }
        elseif( $k === 'formatter' ) {
            return $this->getFormatter();
        }
        throw new Exception( "$k is not defined." );
    }

}
