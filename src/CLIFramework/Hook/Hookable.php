<?php
namespace CLIFramework\Hook;

interface Hookable
{
    public function addHook($name, \Closure $callback);
    public function callHook($name /** , $args...*/);

    /**
     * @return array
     */
    public function getHookPoints();
}
