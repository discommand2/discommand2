<?php
$logOptions = [
    'path' => 'php://stdout',
    'level' => \Monolog\Level::Debug
];

try {
    require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Discommand2.php');
} catch (\Throwable $th) {
    echo ("[ERROR] Missing required files src/Discommand2.php not found. Did you run composer install?\n");
    exit(1);
}

$discommand2 = new \Discommand2\Discommand2($argv, $logOptions);
$discommand2->run();
