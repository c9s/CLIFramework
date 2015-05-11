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

$prompter = new CLIFramework\Prompter();
$line = $prompter->password('Your Password:');
echo "input value: ";
var_dump($line); 
