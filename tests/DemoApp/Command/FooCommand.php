<?php
namespace DemoApp\Command;

class FooCommand extends \CLIFramework\Command {

    public function brief() { return 'brief of foo'; }

    public function init() {
        $this->command('subfoo','DemoApp\\Command\\FooCommand\\SubFooCommand');
    }

    public function execute() {
        $this->getLogger()->info('executing foo command.');
    }
}

