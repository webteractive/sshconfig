<?php

namespace App\Actions;

class GetSshConfigBackupPathAction
{
    public function handle(string $configPath): string
    {
        $directory = dirname($configPath);
        $filename = basename($configPath);
        $timestamp = date('Y-m-d_His');

        return $directory.'/'.$filename.'.backup.'.$timestamp;
    }
}
