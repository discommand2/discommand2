<?php

namespace Discommand2;

use Monolog\Logger;
use Discommand2\Core\Config;

class Discommand2
{
    public function __construct(private Logger $log)
    {
        $this->log->debug("Discommand2 initialized!");
    }

    public function run(array $argv): bool
    {
        $this->log->debug("Discommand2 running with args " . print_r($argv, true));
        switch ($argv[1] ?? '') {
            case 'update':
                return $this->update($argv);
            case 'upgrade':
                return $this->upgrade($argv);
            case 'install':
                return $this->install($argv);
            case 'remove':
                return $this->remove($argv);
            case 'create':
                return $this->create($argv);
            case 'start':
                return $this->start($argv);
            case 'delete':
                return $this->delete($argv);
            default:
                echo "Usage: discommand2 [install|update|upgrade|remove|create|start|delete]\n";
        }
        return true;
    }

    public function update($argv): bool
    {
        if (isset($argv[2]) && $argv[2] != '') {
            $plugin = ' discommand2/' . $argv[2];
            $this->log->info("Updating $plugin...");
        } else {
            $this->log->info("Updating everything...");
            $plugin = '';
        }
        return Composer::command('update' . $plugin);
    }

    public function upgrade($argv): bool
    {
        [$plugin, $force] = $this->validateUpgrade($argv);
        if (!$force) {
            $confirmation = readline("[WARNING] Are you sure you want to upgrade$plugin beyond the current stable version? Please type 'yes' exactly to confirm: ");
            if ($confirmation !== 'yes') {
                $this->log->error("Upgrade Aborted!");
                return false;
            }
        }
        $this->log->info("Upgrading$plugin...");
        return Composer::command('upgrade' . $plugin);
    }

    public function validateUpgrade($argv): array
    {
        $plugin = '';
        $force = false;
        if (isset($argv[2]) && $argv[2] != '') {
            if ($argv[2] !== 'force') {
                $force = isset($argv[3]) && $argv[3] === 'force';
                if (strpos($argv[2], '/') === false) $argv[2] = 'discommand2/' . $argv[2];
                $plugin = ' ' . $argv[2];
            } else {
                $force = true;
            }
        }
        return [$plugin, $force];
    }

    public function install($argv): bool
    {
        if (!isset($argv[2]) || $argv[2] === '') throw new \Exception("Plugin name not specified!");
        $this->log->info("Installing " . $argv[2] . "...");
        // if the argument doesn't already include a / then prepend discommand2/
        if (strpos($argv[2], '/') === false) $argv[2] = 'discommand2/' . $argv[2];
        return Composer::command('require ' . ($argv[2]));
    }

    public function remove($argv): bool
    {
        if (!isset($argv[2]) || $argv[2] === '') throw new \Exception("Plugin name not specified!");
        $this->log->info("Removing " . $argv[2] . "...");
        return Composer::command('remove discommand2/' . ($argv[2]));
    }

    public function create($argv): bool
    {
        [$brainName, $brainPath] = $this->validateCreate($argv);
        $url = $this->createFromTemplate($argv);
        Git::command("submodule add -b main -f $url $brainPath") or throw new \Exception("Failed to clone $url");
        Composer::command("install --working-dir=$brainPath") or throw new \Exception("Failed to install dependencies for $brainName");
        $this->log->info("Brain $brainName created successfully!");
        return true;
    }

    public function validateCreate($argv): array
    {
        if (!isset($argv[2])) throw new \Exception("Brain name not specified!");
        if (!$this->validateBrainName($argv[2])) throw new \Exception("Invalid brain name!");
        $brainName = $argv[2];
        $basePath = Config::get('paths', 'brains');
        $brainPath = $basePath . '/' . $brainName;
        if (file_exists($brainPath)) throw new \Exception("Brain already exists! use config, start, or delete instead.");
        return [$brainName, $brainPath];
    }

    public function createFromTemplate($argv): string
    {
        if (!isset($argv[3]) || $argv[3] !== '') $argv[3] = "brain-template";
        if (strpos($argv[3], '/') === false) $argv[3] = 'discommand2/' . $argv[3];
        if (strpos($argv[3], 'https://') === 0) $url = $argv[3];
        else $url = 'https://github.com/' . $argv[3] . '.git';
        $this->log->info("Creating {$argv[2]} from template " . $url);
        return $url;
    }

    public function start($argv): bool
    {
        if (!isset($argv[2]) || $argv[2] === '') throw new \Exception("Brain name not specified!");
        $this->log->info("Waking " . $argv[2]);
        // todo: start brain
        return true;
    }

    public function delete($argv): bool
    {
        $brain = '';
        $force = false;

        if (isset($argv[2]) && $argv[2] != '') {
            if ($argv[2] !== 'force') {
                $this->log->info("Deleting " . $argv[2]);
                $brain = $argv[2];
                $force = isset($argv[3]) && $argv[3] === 'force';
            } else {
                $force = true;
            }
        } else {
            $this->log->error("Brain name is required");
            return;
        }

        if (!$force) {
            $confirmation = readline("[WARNING] Are you sure you want to delete " . $brain . " including their home directory, sql database(s), message history, and settings? Please type 'yes' exactly to confirm: ");
            if ($confirmation !== 'yes') {
                $this->log->error("Delete Aborted!");
                return;
            }
        }

        $this->log->info("Deleting " . $brain);
        // todo: delete brain
        return true;
    }

    public function validateBrainName($name): bool
    {
        // must be a valid linux username/foldername (no spaces, no special characters except _ and -)
        return preg_match('/^[a-z0-9_-]+$/i', $name);
    }
}
