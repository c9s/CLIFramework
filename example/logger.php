<?php
require 'Universal/ClassLoader/BasePathClassLoader.php';
$classloader = new \Universal\ClassLoader\BasePathClassLoader(array( 
    'src', 'tests' , 'vendor/pear' ));
$classloader->useIncludePath(true);
$classloader->register();
require 'example/app.php';

$app = new ExampleApplication;
$logger = $app->getLogger();
$logger->info('info message');
$logger->debug('debug message');
$logger->notice('notice message');
$logger->warn('warning message');
