<?php
require 'vendor/autoload.php';

use CLIFramework\Logger\ActionLogger;
use CLIFramework\Formatter;

$logger = new ActionLogger(fopen('php://stderr','w'), new Formatter);


foreach (['ProductSchema','OrderSchema', 'OrderItemSchema'] as $title) {
    $logAction = $logger->newAction($title, 'Update schema class files...');
    foreach (['checking', 'updating', 'pulling'] as $status) {
        $logAction->setStatus($status);
        sleep(1);
    }
    $logAction->done();
}
