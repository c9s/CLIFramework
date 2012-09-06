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
use CLIFramework\Formatter;

class Logger
{
    /*
     * log level
     *
     * critical error = 1
     * error          = 2
     * warn           = 3
     * info           = 4
     * info2          = 5
     * debug          = 6
     * debug2         = 7
     * */
    public $logLevels = array(
        'critical' => 1,
        'error'    => 2,
        'warn'     => 3,
        'info' => 4,
        'info2' => 5,
        'debug' => 6,
        'debug2' => 7,
    );

    public $levelStyles = array(
        'critical' => 'strong_red',
        'error'    => 'strong_red',
        'warn'     => 'red',
        'info'     => 'strong_green',
        'info1'    => 'green',
        'debug'    => 'strong_white',
        'debug2'   => 'white',
    );

    /**
     * current level
     *
     * any message level lower than this will be displayed.
     * */
    public $level = 4;

    /**
     * foramtter class
     *
     * @var CLIFramework\Formatter
     */
    public $formatter;

    public function __construct()
    {
        $this->formatter = new Formatter;
    }

    public function setLevel($level, $indent = 0)
    {
        $this->level = $level;
    }

    public function quiet()
    {
        $this->level = 0;
    }

    public function setVerbose()
    {
        $this->level = $this->getLevelByName('info2');
    }

    public function setDebug()
    {
        $this->level = $this->getLevelByName('debug2');
    }

    public function setFormatter( $formatter )
    {
        $this->formatter = $formatter;
    }

    public function getFormatter()
    {
        return $this->formatter;
    }

    public function __call($method,$args)
    {
        $msg = $args[0];
        $indent = isset($args[1]) ? $args[1] : 0;
        $level = $this->getLevelByName($method);
        $style = $this->getStyleByName($method);
        if ($level > $this->level) {
            // do not print.
            return;
        }

        if( $this->level <= 4 && $level >= 4 )
            $style = 'white';

        if( $indent )
            echo str_repeat("\t", $indent);

        /* detect object */
        if ( is_object($msg) || is_array($msg) ) {
            echo $this->formatter->format( print_r( $msg , 1 ) , $style ) . "\n";
        } else {
            echo $this->formatter->format( $msg , $style ) , "\n";
        }
    }

    public function getStyleByName($levelName)
    {
        return @$this->levelStyles[$levelName];
    }

    public function getLevelByName($levelName)
    {
        return @$this->logLevels[$levelName];
    }

    public static function getInstance()
    {
        static $instance;

        return $instance ? $instance : $instance = new static;
    }
}
