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


    public function criticalError($msg)
    {
        $this->_print($msg,'strong_red');
    }

    public function error($msg)
    {
        $this->_print($msg,'strong_red');
    }

    public function warn($msg,$indent = 0)
    {
        $this->_print($msg,'yellow',$indent);
    }

    public function info($msg,$indent = 0)
    {
        $style = $this->level > 4 ? 'strong_green' : 'white';
        $this->_print($msg, $style ,$indent);
    }

    public function info2($msg,$indent = 0) 
    {
        $style = $this->level > 4 ? 'green' : 'white';
        $this->_print($msg, $style ,$indent);
    }

    public function debug($msg,$indent = 0)
    {
        $this->_print($msg,'white',$indent);
    }

    public function debug2($msg,$indent = 0)
    {
        $this->_print($msg,'strong_white',$indent);
    }

    public function getLevelByName($style_name)
    {
        return @$this->logLevels[ $style_name ];
    }

    private function _print($msg,$style,$indent = 0) 
    {
        $level = $this->getLevelByName( $style );
        if( $level > $this->level ) {
            // do not print.
            return;
        }

        echo str_repeat("\t", $indent);

        /* detect object */
        if( is_object($msg) || is_array($msg) )  {
            echo $this->formatter->format( print_r( $msg , 1 ) , $style ) . "\n";
        } else {
            echo $this->formatter->format( $msg , $style ) , "\n";
        }
    }
}


