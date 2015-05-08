<?php
namespace CLIFramework\IO;

class EchoWriter implements Writer
{
    public function write($text)
    {
        echo $text;
    }

    public function writeln($text)
    {
        echo $text . "\n";
    }

    public function writef($format)
    {
        $args = func_get_args();
        $this->write(call_user_func_array('sprintf', $args));
    }
}
