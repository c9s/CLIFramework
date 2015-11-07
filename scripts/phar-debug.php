<?php
if (isset($argv[1])) {
    $file = $argv[1];
} else {
    $file = 'app.phar';
}
$phar = new Phar($file, 0);
foreach (new RecursiveIteratorIterator($phar) as $file) {
    echo $file->getPathname(), PHP_EOL;
}
echo "Stub:\n";
echo $phar->getStub();
