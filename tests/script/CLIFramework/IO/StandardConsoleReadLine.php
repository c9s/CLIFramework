<?php
require __DIR__ . '/../../../bootstrap.php';

$stty = new CLIFramework\IO\NullStty();
$input = new CLIFramework\IO\StandardConsole($stty);
echo $input->readLine('');
