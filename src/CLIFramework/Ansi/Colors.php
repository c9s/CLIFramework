<?php
namespace CLIFramework\Ansi;
use InvalidArgumentException;

/**
 * ANSI Color definitions
 *
 * @author Yo-An Lin <yoanlin93@gmail.com>
 */
class Colors
{
    const CODE_PATTERN = '#\033\[\d{0,1}[,;]?\d*(?:;\d2)?m#x';


    static protected $foregroundColors = array(
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    );

    static protected $backgroundColors = array(
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    );

    static protected $attributes = array(
        'bold'      => 1,
        'dim'       => 2,
        'underline' => 4,
        'blink'     => 5,
        'reverse'   => 7,
        'hidden'    => 8,
    );

    static public function stripAnsiEscapeCode($str) {
        return preg_replace(self::CODE_PATTERN, '', $str);
    }

    static public function strlenWithoutAnsiEscapeCode($str) {
        $plain = preg_replace(self::CODE_PATTERN, '', $str);
        return mb_strlen($plain);
    }

    static public function reset()
    {
        return "\033[0m";
    }

    static public function attribute($text, $attribute)
    {
        if (!isset(self::$attributes[$attribute])) {
            throw new InvalidArgumentException("Undefined attribute $attribute");
        }
        $str = "\033[" . self::$attributes[$attribute] . "m";
        $str .= $text;
        $str = "\033[0m";
        return $str;
    }

    // Returns colored string
    static public function decorate($string, $fg = null, $bg = null, $attribute = null)
    {
        $coloredString = "";

        // Check if given foreground color found
        if ($fg && isset(self::$foregroundColors[$fg])) {
            $coloredString .= "\033[" . self::$foregroundColors[$fg] . "m";
        }
        // Check if given background color found
        if ($bg && isset(self::$backgroundColors[$bg])) {
            $coloredString .= "\033[" . self::$backgroundColors[$bg] . "m";
        }

        if ($attribute) {
            $coloredString .= "\033[" . self::$attributes[$attribute] . "m";
        }

        // Add string and end coloring
        $coloredString .=  $string;

        if ($fg || $bg) {
            $coloredString .= "\033[0m";
        }
        return $coloredString;
    }

    // Returns all foreground color names
    static public function getForegroundColors() {
        return array_keys(self::$foregroundColors);
    }

    // Returns all background color names
    static public function getBackgroundColors() {
        return array_keys(self::$backgroundColors);
    }
}



