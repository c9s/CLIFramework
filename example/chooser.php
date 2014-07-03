<?php
/*
 * This file is part of the CLIFramework package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
require 'vendor/autoload.php';

$app = new ExampleApplication;
$line = $app->ask('Your Name:',array('John','Mary'));
echo "input value: ";
var_dump($line); 
$val = $app->choose('Your versions:' , array( 
    'php-5.4.0' => '5.4.0',
    'php-5.4.1' => '5.4.1',
    'system' => '5.3.0',
));
var_dump($val); 
