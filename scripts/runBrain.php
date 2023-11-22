<?php

namespace Discommand2\Core;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function initialize($argv)
{
    if (!isset($argv[1])) {
        throw new \InvalidArgumentException("Missing required argument: brain name");
    }

    $myName = $argv[1];

    try {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
    } catch (\Throwable $e) {
        throw new \RuntimeException($e->getMessage() . PHP_EOL . "Please run: discommand2 install");
    }

    $homeDir = Config::get('homeDir');
    if (is_null($homeDir)) {
        throw new \RuntimeException("homeDir not defined in config" . DIRECTORY_SEPARATOR . "discommand2.json");
    }
    if (!is_dir($homeDir)) {
        throw new \RuntimeException("$homeDir is not a directory, please check config" . DIRECTORY_SEPARATOR . "discommand2.json");
    }

    $homeDir .= DIRECTORY_SEPARATOR . $myName;
    if (!is_dir($homeDir)) {
        throw new \RuntimeException("Brain not found. Please run: discommand2 create $myName");
    }

    $path = Config::get('logger', 'path') ?? 'php://stdout';
    $level = match (substr(strtoupper(Config::get('logger', 'level')), 0, 3)) {
        'DEB' => Level::Debug,
        'INF' => Level::Info,
        'NOT' => Level::Notice,
        'WAR' => Level::Warning,
        'ERR' => Level::Error,
        'CRI' => Level::Critical,
        'ALE' => Level::Alert,
        'EME' => Level::Emergency,
        default => Level::Info
    };

    $logger = new Logger($myName);
    $logger->pushHandler(new StreamHandler($path, $level));

    return new Brain($logger, $myName);
}

$brain = initialize($argv);
$brain->think();
