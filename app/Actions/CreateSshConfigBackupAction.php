<?php

namespace App\Actions;

use Illuminate\Support\Facades\File;

class CreateSshConfigBackupAction
{
    public function handle(string $configPath): string
    {
        if (! File::exists($configPath)) {
            throw new \RuntimeException("Config file does not exist: {$configPath}");
        }

        $backupPath = app(GetSshConfigBackupPathAction::class)->handle($configPath);

        File::copy($configPath, $backupPath);

        return $backupPath;
    }
}
