<?php
$loader = require 'vendor/autoload.php';
$loader->add('TestApp','tests');
$loader->add('DemoApp','tests');
$container = \CLIFramework\ServiceContainer::getInstance();
// $container['logger']->setQuiet();
