<?php
use CLIFramework\Ansi\Colors;

class ColorsTest extends PHPUnit_Framework_TestCase
{
    public function stringProvider()
    {
        $data = array();
        foreach(Colors::getForegroundColors() as $fg) {
            foreach(Colors::getBackgroundColors() as $bg) {
                $data[] = array("Hello \n\t World", $fg, $bg);
            }
        }
        return $data;
    }

    public function printColors() {
        echo "\n";
        foreach(Colors::getForegroundColors() as $fg) {
            foreach(Colors::getBackgroundColors() as $bg) {
                echo Colors::decorate("Hello", $fg, $bg);
            }
            echo "\n";
        }
    }

    /**
     * @dataProvider stringProvider
     */
    public function testStrlenWithoutAnsiEscapeCode($input, $fg, $bg)
    {
        $str = Colors::decorate($input, $fg, $bg);
        $len = Colors::strlenWithoutAnsiEscapeCode($str);
        $this->assertEquals(strlen($input), $len);
    }

    /**
     * @dataProvider stringProvider
     */
    public function testStripAnsiEscapeCode($input, $fg, $bg)
    {
        $str = Colors::decorate($input, $fg, $bg);
        $output = Colors::stripAnsiEscapeCode($str);
        $this->assertEquals($input, $output);
    }
}

