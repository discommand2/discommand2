<?php

namespace Discommand2;

class Composer
{
    static function which_composer()
    {
        $composer = trim(shell_exec('which composer'));
        if (!$composer) $composer = trim(shell_exec('which composer.phar'));
        return $composer;
    }

    static function install()
    {
        $composer = self::which_composer();
        if (!$composer) {
            echo "[INFO] Composer not found, installing...";
            shell_exec('curl -sS https://getcomposer.org/installer | php');
            if (!file_exists(__DIR__ . '/composer.phar')) {
                echo "[ERROR] Composer download failed!\n";
                exit(1);
            }
            echo " done!\n";
            $composer = __DIR__ . '/composer.phar';
        }
        echo "[INFO] Installing dependencies...";
        shell_exec("export COMPOSER_ALLOW_SUPERUSER=1 && export COMPOSER_NO_INTERACTION=1 && $composer install 2>&1");
        if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
            echo "[ERROR] Composer install failed\n";
            exit(1);
        }
        echo " done!\n";
    }
}
