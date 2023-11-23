<?php

namespace Discommand2;

use Exception;
use Discommand2\Core\Config;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LogFactory
{
    static function create($name): Logger
    {
        $log = self::createLogger($name);
        $base_path = self::getBasePath();
        $configs = self::getConfigs();

        foreach ($configs as $config) {
            self::validateConfig($config);
            $path = self::validatePath($config['path'], $base_path);
            $level = self::validateLevel($config['level']);
            $log->pushHandler(new StreamHandler($path, $level));
        }

        $log->debug("Log initialized!");
        return $log;
    }

    static function createLogger($name): Logger
    {
        return new Logger($name);
    }

    static function getBasePath(): string
    {
        $base_path = Config::get('paths', 'logs');
        if (substr($base_path, 0, 1) != '/') {
            $base_path = __DIR__ . '/../' . $base_path;
        }
        if (!file_exists($base_path)) {
            shell_exec("mkdir -p $base_path");
        }
        return $base_path;
    }

    static function getConfigs(): array
    {
        return Config::get('logs');
    }

    static function validateConfig(array $config): void
    {
        if (!isset($config['path'], $config['level'])) {
            throw new Exception("Logging path or level not defined in config/log.json!");
        }
    }

    static function validatePath($path, $base_path)
    {
        if ($path === 'php://stdout' || $path === 'php://stderr') {
            return $path;
        }
        if (substr($path, 0, 1) != '/') {
            $path = $base_path . '/' . $path;
        }
        if (!is_dir(dirname($path))) {
            shell_exec("mkdir -p " . dirname($path));
        }
        return $path;
    }

    static function validateLevel($level)
    {
        return match ($level) {
            'DEBUG' => Level::Debug,
            'INFO' => Level::Info,
            'NOTICE' => Level::Notice,
            'WARNING' => Level::Warning,
            'ERROR' => Level::Error,
            'CRITICAL' => Level::Critical,
            'ALERT' => Level::Alert,
            'EMERGENCY' => Level::Emergency,
            default => throw new Exception("Invalid logging level ($level) set in config/logging.json!"),
        };
    }
}
