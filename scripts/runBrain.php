<?php

namespace Discommand2\Core;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Discommand2\Core\Brain;


require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Check if the first argument is set, if not, terminate the script with a message
if (!isset($argv[1]) || empty(trim($argv[1]))) {
    echo ("Please provide the name of the brain to run.\n");
    exit(1);
}

$myName = $argv[1];

// Create a new instance of Logger
$logger = new Logger($myName);

// Create a new instance of StreamHandler STDOUT
$streamHandler = new StreamHandler('php://stdout', Level::Debug);

// Add the StreamHandler to the Logger
$logger->pushHandler($streamHandler);

// Create a new instance of Core with the provided name
$brain = new Brain($logger, $myName);
