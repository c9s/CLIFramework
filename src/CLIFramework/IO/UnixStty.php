<?php
namespace CLIFramework\IO;

class UnixStty implements Stty
{
    public function enableEcho()
    {
        shell_exec('stty echo');
    }

    public function disableEcho()
    {
        shell_exec('stty -echo');
    }

    public function dump()
    {
        return shell_exec('stty -g');
    }

    public function withoutEcho(\Closure $callback)
    {
        $oldStyle = $this->dump();
        // don't display characters from user input.
        $this->disableEcho();
        $result = null;

        try {
            $result = $callback();
            $this->restoreStyle($oldStyle);
        } catch (\Exception $e) {
            $this->restoreStyle($oldStyle);
            throw $e;
        }

        return $result;
    }

    private function restoreStyle($style)
    {
        if (is_null($style)) {
            return;
        }

        shell_exec('stty ' . $style);
    }
}
