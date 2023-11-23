<?php

namespace Discommand2;

class Git
{
    static function which_git(): string
    {
        $git = trim(shell_exec('which git') ?? '');
        if ($git === '') throw new \Exception("Git not found, Please use your package manager to install git!");
        return $git;
    }

    static function command(string $command): bool
    {
        $git = self::which_git();
        $last_line = exec("$git $command 2>&1", $output, $exit_code);
        if ($exit_code !== 0) throw new \Exception("Git command failed: $last_line");
        return true;
    }
}
