<?php

namespace App\Actions;

use App\Models\SshConfig;

class SyncSshConfigFromFileAction
{
    public function handle(string $configPath): void
    {
        $parseAction = app(ParseSshConfigAction::class);
        $configs = $parseAction->handle($configPath);

        foreach ($configs as $config) {
            $host = $config['host'];
            $existing = SshConfig::where('host', $host)->first();

            if ($existing) {
                $existing->update([
                    'properties' => $config['properties'],
                    'raw_block' => $config['raw_block'] ?? null,
                ]);
            } else {
                SshConfig::create([
                    'host' => $host,
                    'properties' => $config['properties'],
                    'raw_block' => $config['raw_block'] ?? null,
                ]);
            }
        }
    }
}
