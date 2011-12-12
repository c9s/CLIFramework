<?php
require 'tests/helpers.php';
require 'UniversalClassLoader/SplClassLoader.php';
$classLoader = new \UniversalClassLoader\SplClassLoader(array( 
    'Onion' => 'src',
    'CLIFramework' => 'src',
    'TestApp' => 'tests',
));
$classLoader->useIncludePath(true);
$classLoader->register();
