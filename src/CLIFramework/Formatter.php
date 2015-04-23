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

/**
 * Console output formatter class
 *
 *
 *   $formatter = new Formatter;
 *   $text = $formatter->format( 'text', 'styleName' );
 *   $text = $formatter->format( 'text', 'red' );
 *   $text = $formatter->format( 'text', 'green' );
 *
 */
class Formatter
{

    // Refactor style builder out.
    protected $styles = array(
        'dim'          => array('dim' => 1),
        'red'          => array('fg' => 'red'),
        'green'        => array('fg' => 'green'),
        'white'        => array('fg' => 'white'),
        'yellow'       => array('fg' => 'yellow'),
        'strong_red'   => array('fg' => 'red',     'bold'  => 1),
        'strong_green' => array('fg' => 'green',   'bold' => 1),
        'strong_white' => array('fg' => 'white',   'bold' => 1),
        'ask'          => array('fg' => 'white',   'bold' => 1 , 'underline' => 1 ),
        'choose'       => array('fg' => 'white',   'bold' => 1 , 'underline' => 1 ),

        'bold' => array('fg' => 'white', 'bold' => 1 ),
        'underline' => array( 'fg' => 'white' , 'underline' => 1 ),

        // generic styles for logger
        'info'   => array('fg'  => 'white', 'bold' => 1 ),
        'debug'  => array('fg'  => 'white' ),
        'notice' => array('fg'  => 'yellow' ),
        'warn'   => array('fg'  => 'red' ),
        'error'  => array('fg'  => 'red', 'bold'   => 1 ),

        'done'    => array('fg' => 'black', 'bg' => 'green' ),
        'success' => array('fg' => 'black', 'bg' => 'green' ),
        'fail'    => array('fg' => 'black', 'bg' => 'red' ),
    );

    protected $options = array(
        'bold' => 1,
        'dim' => 2,
        'underline' => 4,
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
        $this->supportsColors = DIRECTORY_SEPARATOR != '\\'
                && function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }

    public function preferRawOutput()
    {
        $this->supportsColors = false;
    }

    public function addStyle( $name, $style )
    {
        $this->styles[ $name ] = $style;
    }

    public function hasStyle( $name )
    {
        return isset($this->styles[ $name ]);
    }

    public function getStartMark($style)
    {
        if (!$this->supportsColors) {
            return;
        }

        if ($style == 'none' || ! isset($this->styles[$style])) {
            return '';
        }

        $parameters = $this->styles[$style];
        $codes = array();

        if (isset($parameters['fg'])) {
            $codes[] = $this->foreground[$parameters['fg']];
        }

        if (isset($parameters['bg'])) {
            $codes[] = $this->background[$parameters['bg']];
        }

        foreach ($this->options as $option => $value) {
            if (isset($parameters[$option]) && $parameters[$option]) {
                $codes[] = $value;
            }
        }

        return "\033[".implode(';', $codes).'m';
    }

    public function getClearMark()
    {
        if (! $this->supportsColors) {
            return '';
        }
        return "\033[0m";
    }

    /**
     * Formats a text according to the given style or parameters.
     *
     * @param string $text  The text to style
     * @param string $style A style name
     *
     * @return string The styled text
     */
    public function format($text = '', $style = 'none')
    {
        return $this->getStartMark($style) . $text . $this->getClearMark();
    }
}
