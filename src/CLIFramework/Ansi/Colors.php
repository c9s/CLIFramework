<?php
namespace CLIFramework\Ansi;

/**
 * ANSI Color definitions
 *
 * @codeCoverageIgnore
 * @author Yo-An Lin <yoanlin93@gmail.com>
 */
class Colors
{
    protected $foregroundColors = array(
        'black'        => '0,30',
        'dark_gray'    => '1,30',
        'blue'         => '0,34',
        'light_blue'   => '1,34',
        'green'        => '0,32',
        'light_green'  => '1,32',
        'cyan'         => '0,36',
        'light_cyan'   => '1,36',
        'red'          => '0,31',
        'light_red'    => '1,31',
        'purple'       => '0,35',
        'light_purple' => '1,35',
        'brown'        => '0,33',
        'yellow'       => '1,33',
        'light_gray'   => '0,37',
        'white'        => '1,37',
    );

    protected $backgroundColors = array(
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    );

    // Returns colored string
    public function wrap($string, $fg = null, $bg = null) {
        $coloredString = "";

        // Check if given foreground color found
        if ($fg && isset($this->foregroundColors[$fg])) {
            $coloredString .= "\033[" . $this->foregroundColors[$fg] . "m";
        }
        // Check if given background color found
        if ($bg && isset($this->backgroundColors[$bg])) {
            $coloredString .= "\033[" . $this->backgroundColors[$bg] . "m";
        }

        // Add string and end coloring
        $coloredString .=  $string;

        if ($fg || $bg) {
            $coloredString .= "\033[0m";
        }
        return $coloredString;
    }

    // Returns all foreground color names
    public function getForegroundColors() {
        return array_keys($this->foregroundColors);
    }

    // Returns all background color names
    public function getBackgroundColors() {
        return array_keys($this->backgroundColors);
    }

}



