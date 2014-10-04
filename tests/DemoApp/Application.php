<?php
namespace DemoApp;

class Application extends \CLIFramework\Application {
    public function init()
    {
        parent::init();
        $this->command('foo');
        $this->command('commit');
        $this->topic('basic');
    }
}


