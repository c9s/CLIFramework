<?php
namespace CLIFramework\IO;

class NullStty implements Stty
{
    public function enableEcho()
    {
    }

    public function disableEcho()
    {
    }

    public function dump()
    {
        return '';
    }

    public function withoutEcho(\Closure $callback)
    {
        return $callback();
    }
}
