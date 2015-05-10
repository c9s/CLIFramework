<?php
require __DIR__ . '/../../../bootstrap.php';

$stty = new CLIFramework\IO\NullStty();
$input = new CLIFramework\IO\StandardConsole($stty);
$line = $input->readPassword('');
echo $line;
