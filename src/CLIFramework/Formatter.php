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

class Formatter
{

    protected $styles = array(
        'error'    => array('fg' => 'red'),
        'error2'    => array('fg' => 'red', 'bold' => 1),
        'warn'     => array('fg' => 'red'),
        'debug'    => array('fg' => 'white'),
        'debug2'    => array('fg' => 'white','bold' => 1),
        'info'     => array('fg' => 'green'),
        'info2'    => array('fg' => 'green','bold' => 1),
    );

    protected $options = array(
        'bold' => 1, 
        'underscore' => 4, 
        'blink' => 5, 
        'reverse' => 7, 
        'conceal' => 8
    );

    protected $foreground = array(
        'black' => 30, 
        'red' => 31, 
        'green' => 32, 
        'yellow' => 33, 
        'blue' => 34, 
        'magenta' => 35, 
        'cyan' => 36, 
        'white' => 37
    );

    protected $background = array(
        'black' => 40, 
        'red' => 41, 
        'green' => 42, 
        'yellow' => 43, 
        'blue' => 44, 
        'magenta' => 45, 
        'cyan' => 46, 
        'white' => 47
    );

    protected $supportsColors;

    public function __construct()
    {
        $this->supportsColors = DIRECTORY_SEPARATOR != '\\' && function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }


    public function addStyle( $name, $style )
    {
        $this->styles[ $name ] = $style;
    }


    /**
     * Formats a text according to the given style or parameters.
     *
     * @param  string   $text  The text to style
     * @param  string   $style A style name
     *
     * @return string The styled text
     */
    public function format($text = '', $style = 'none')
    {
        if (!$this->supportsColors)
            return $text;

        if ( $style == 'none' || ! isset($this->styles[$style]) )
            return $text;

        $parameters = $this->styles[$style];
        $codes = array();

        if (isset($parameters['fg']))
            $codes[] = $this->foreground[$parameters['fg']];

        if (isset($parameters['bg']))
            $codes[] = $this->background[$parameters['bg']];

        foreach ( $this->options as $option => $value ) {
            if (isset($parameters[$option]) && $parameters[$option]) {
                $codes[] = $value;
            }
        }

        return "\033[".implode(';', $codes).'m'.$text."\033[0m";
    }

}

