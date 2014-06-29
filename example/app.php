<?php
class BarCommand extends CLIFramework\Command {

    public function brief() { return 'brief of bar'; }

	public function options($opts) {
		$opts->add('v','verbose flag')->is('boolean');
		$opts->add('x|extra','extra flag');
		$opts->add('f|file:','file option')->is('file');
	}
	public function execute() {
		$this->getLogger()->notice('executing bar command.');
	}
}
class FooCommand extends CLIFramework\Command { 

    public function brief() { return 'brief of foo'; }

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

