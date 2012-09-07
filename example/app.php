<?php
class BarCommand extends CLIFramework\Command {
	public function options($opts) {
		$opts->add('x|extra','extra options');
	}
	public function execute() {
		$this->getLogger()->notice('executing bar command.');
	}
}
class FooCommand extends CLIFramework\Command { 
	public function execute() {
		$this->getLogger()->warn('executing foo command.');
	}
}

class ExampleApplication extends CLIFramework\Application {

    public function init()
    {
        parent::init();
        $this->registerCommand('foo','FooCommand');
        $this->registerCommand('bar','BarCommand');
    }
}

