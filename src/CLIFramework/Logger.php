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
     *
     * */

    /* current level */
    public $level = 7;

    /* foramtter class */
	public $formatter;

	public function __construct()
	{
		$this->formatter = new Formatter;
	}

    public function setLevel($level, $indent = 0)
    {
        $this->level = $level;
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
        $this->_print($msg,'error');
    }

    public function error($msg)
    {
        $this->_print($msg,'error2');
    }

    public function warn($msg,$indent = 0)
    {
        $this->_print($msg,'warn',$indent);
    }

    public function info($msg,$indent = 0)
    {
        $this->_print($msg,'info',$indent);
    }

    public function info2($msg,$indent = 0) 
    {
        $this->_print($msg,'info2',$indent);
    }

    public function debug($msg,$indent = 0)
    {
        $this->_print($msg,'debug',$indent);
    }

    public function debug2($msg,$indent = 0)
    {
        $this->_print($msg,'debug2',$indent);
    }

    private function _print($msg,$style,$indent = 0) 
    {
        echo str_repeat("\t", $indent);

        /* detect object */
        if( is_object($msg) || is_array($msg) )  {
            echo $this->formatter->format( print_r( $msg , 1 ) , $style ) . "\n";
        } else {
            echo $this->formatter->format( $msg , $style ) , "\n";
        }
    }

}


