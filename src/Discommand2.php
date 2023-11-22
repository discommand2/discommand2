<?php

namespace Discommand2;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Discommand2\Core\Brain;

class Discommand2
{

    private $myName;
    private $brain;

    public function __construct($argv, private $logOptions = ['path' => 'php://stdout', 'level' => Level::Info])
    {
        $this->myName = $this->checkFirstArgument($argv);
        $this->loadAutoload($this->getAutoloadPath());
        $this->brain = $this->createBrainInstance($this->myName, $this->logOptions);
    }

    public function run()
    {
        $this->brain->run();
    }

    private function checkFirstArgument($argv)
    {
        if (!isset($argv[1]) || empty(trim($argv[1]))) {
            echo ("Please provide the name of the brain to run.\n");
            exit(1);
        }
        return $argv[1];
    }

    private function getAutoloadPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    private function runComposerCommand($command)
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

    private function loadAutoload($autoloadPath)
    {
        if (!file_exists($autoloadPath)) {
            echo ("[INFO] vendor/autoload.php not found. Installing dependencies");
            $this->runComposerCommand('install');
        }

        try {
            require_once $autoloadPath;
        } catch (\Exception $e) {
            echo ("[ERROR] Failed to load vendor/autoload.php: " . $e->getMessage());
            echo ("[INFO] Regenerating autoload files");
            $this->runComposerCommand('dump-autoload');
            try {
                require_once $autoloadPath;
            } catch (\Exception $e) {
                echo ("[ERROR] Failed to load vendor/autoload.php even after attempting to regenerate it: " . $e->getMessage());
                exit(1);
            }
        }
    }

    private function createBrainInstance($myName, $logOptions)
    {
        return new Brain((new Logger($myName))->pushHandler(new StreamHandler($logOptions['path'], $logOptions['level'])), $myName);
    }
}
