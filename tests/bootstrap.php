<?php
require 'tests/helpers.php';
require 'vendor/pear/Universal/ClassLoader/SplClassLoader.php';
$classLoader = new \Universal\ClassLoader\SplClassLoader(array( 
    'CLIFramework' => 'src',
    'TestApp' => 'tests',
));
$classLoader->useIncludePath(true);
$classLoader->register();
