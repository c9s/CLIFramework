<?php
namespace DemoApp\Command\FooCommand;

class SubFooCommand extends \CLIFramework\Command {

    public function brief() { return 'brief of subfoo'; }

    public function options($opts) {
        $opts->add('x', 'x desc');
        $opts->add('y', 'y desc');
        $opts->add('z', 'z desc');
    }

    public function arguments($args) {
        $args->add('p1');
        $args->add('p2');
    }

    public function execute() {
        $this->logger->info('executing subfoo command.');
    }
}

