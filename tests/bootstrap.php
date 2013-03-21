<?php
require 'tests/helpers.php';
require 'vendor/autoload.php';
require 'vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array( 
    'src', 'vendor/pear', 'tests',
));
$classLoader->useIncludePath(true);
$classLoader->register();
