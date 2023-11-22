<?php

namespace Discommand2\Core;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Discommand2\Core\Brain;

// Check if the first argument is set, if not, terminate the script with a message
if (!isset($argv[1]) || empty(trim($argv[1]))) {
    echo ("Please provide the name of the brain to run.\n");
    exit(1);
}

$myName = $argv[1];

$autoloadPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function runComposerCommand($command)
{
    if (is_executable('composer')) {
        echo ("[INFO] Running composer $command");
        shell_exec('composer ' . $command);
    } else {
        if (!file_exists('composer.phar')) {
            echo ("[INFO] Composer not found. Downloading composer.phar");
            file_put_contents('composer.phar', file_get_contents('https://getcomposer.org/composer-stable.phar'));
        }
        echo ("[INFO] Running php composer.phar $command");
        shell_exec('php composer.phar ' . $command);
    }
}

if (!file_exists($autoloadPath)) {
    echo ("[INFO] vendor/autoload.php not found. Installing dependencies");
    runComposerCommand('install');
}

try {
    require_once $autoloadPath;
} catch (\Exception $e) {
    echo ("[ERROR] Failed to load vendor/autoload.php: " . $e->getMessage());
    echo ("[INFO] Regenerating autoload files");
    runComposerCommand('dump-autoload');
    try {
        require_once $autoloadPath;
    } catch (\Exception $e) {
        echo ("[ERROR] Failed to load vendor/autoload.php even after attempting to regenerate it: " . $e->getMessage());
        exit(1);
    }
}

// Create a new instance of Core with the provided name
$brain = new Brain((new Logger($myName))->pushHandler(new StreamHandler('php://stdout', Level::Debug)), $myName);
