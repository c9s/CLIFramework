<?php
require __DIR__ . '/../../../bootstrap.php';

$stty = new CLIFramework\IO\NullStty();
$input = new CLIFramework\IO\ReadlineConsole($stty);
echo $input->readPassword('');
