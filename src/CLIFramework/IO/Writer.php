<?php
namespace CLIFramework\IO;

interface Writer
{
    /**
     * @param string $text
     */
    public function write($text);

    /**
     * @param string $text
     */
    public function writeln($text);

    /**
     * @param string $format
     */
    public function writef($format);
}
