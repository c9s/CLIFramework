<?php
require 'tests/helpers.php';
require '../Universal/src/Universal/ClassLoader/SplClassLoader.php';
$classLoader = new \Universal\ClassLoader\SplClassLoader(array( 
    'CLIFramework' => 'src',
    'TestApp' => 'tests',
));
$classLoader->useIncludePath(true);
$classLoader->register();
