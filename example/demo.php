<?php
/*
 * This file is part of the {{ }} package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

require 'Universal/ClassLoader/SplClassLoader.php';
$classloader = new \Universal\ClassLoader\SplClassLoader(array( 
    'CLIFramework' => 'src',
    'TestApp' => 'tests'
));
$classloader->useIncludePath(true);
$classloader->register();
$app = new \TestApp\Application;
$app->run($argv);
