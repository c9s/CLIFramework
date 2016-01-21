<?php
/**
 * This file is part of the CLIFramework package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
require 'vendor/autoload.php';

$progress = new CLIFramework\Component\Progress\ProgressBar(STDERR);
$progress->setUnit('bytes');
$progress->start('downloading file');
$total = 512;
for ($i = 0; $i <= $total; $i++) {
    usleep(5 * 10000);
    $progress->updateLayout();
    $progress->update($i, $total);
}
$progress->finish();
