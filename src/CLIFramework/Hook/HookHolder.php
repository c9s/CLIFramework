<?php
namespace CLIFramework\Hook;

class HookHolder implements Hookable
{
    private $hooks = array();

    public function addHook($name, \Closure $callback)
    {
        $this->addHookByArray(array('name' => $name, 'callback' => $callback));
    }

    public function addHookByArray(array $options)
    {
        $name = $options['name'];

        if (!$this->hasHook($name)) {
            $this->hooks[$name] = array();
        }

        $this->hooks[$name][] = $options['callback'];
    }

    public function callHook($name)
    {
        if (!$this->hasHook($name)) {
            return;
        }

        $callbackArgs = array_slice(func_get_args(), 1);
        foreach ($this->hooks[$name] as $callback) {
            call_user_func_array($callback ,$callbackArgs);
        }
    }

    public function getHookPoints()
    {
        return array_keys($this->hooks);
    }

    private function hasHook($name)
    {
        return isset($this->hooks[$name]);
    }
}
