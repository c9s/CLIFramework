<?php
namespace CLIFramework\Extension;

use CLIFramework\ServiceContainer;
use CLIFramework\Command;
use CLIFramework\Hook\Hookable;
use CLIFramework\Hook\HookHolder;
use CLIFramework\Exception\ExtensionException;

abstract class ExtensionBase
    implements Extension, Hookable
{
    private $hooks;

    private function getHooks()
    {
        if (!$this->hooks) {
            $this->hooks = new HookHolder();
        }
        return $this->hooks;
    }

    public function getHookPoints()
    {
        return $this->getHooks()->getHookPoints();
    }

    public function addHook($name, \Closure $callback)
    {
        $this->getHooks()->addHook($name, $callback);
    }

    public function addHookByArray(array $options)
    {
        $this->getHooks()->addHookByArray($options);
    }

    public function callHook($name)
    {
        call_user_func_array(array($this->getHooks(), 'callHook'), func_get_args());
    }
}
