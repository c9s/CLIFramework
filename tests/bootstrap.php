<?php
require 'PHPUnit/TestMore.php';
require 'vendor/autoload.php';
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array( 
    'src', 'vendor/pear', 'tests',
));
$classLoader->useIncludePath(true);
$classLoader->register();
