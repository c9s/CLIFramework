<?php
namespace DemoApp;

class Application extends \CLIFramework\Application {

    const NAME = 'demo';
    const VERSION = '1.0.0';

    public function init()
    {
        parent::init();
        $this->command('foo');
        $this->command('add');
        $this->command('commit');
        $this->command('server');
        $this->topic('basic');
    }
}


