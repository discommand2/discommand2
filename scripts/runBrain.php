<?php

namespace Discommand2\Core;

use Discommand2\Core\Brain;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Check if the first argument is set, if not, terminate the script with a message
if (!isset($argv[1]) || empty(trim($argv[1]))) {
    die("Please provide the name of the brain to run.\n");
}

$myName = $argv[1];

// Create a new instance of Core with the provided name
$brain = new Brain($myName);
