<?php

namespace App\Actions;

use App\Models\SshConfig;

class SyncSshConfigToFileAction
{
    public function handle(): void
    {
        $getPathAction = app(GetConfigPathAction::class);
        $configPath = $getPathAction->handle();

        if (! $configPath) {
            throw new \RuntimeException('SSH config path not set');
        }

        $configs = SshConfig::all()->map(function ($config) {
            return [
                'host' => $config->host,
                'properties' => $config->properties,
            ];
        })->toArray();

        $writeAction = app(WriteSshConfigAction::class);
        $writeAction->handle($configs, $configPath);
    }
}
