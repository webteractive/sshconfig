<?php

namespace App\Actions;

use App\Models\SshConfig;

class SyncSshConfigBothAction
{
    public function handle(): array
    {
        $getPathAction = app(GetConfigPathAction::class);
        $configPath = $getPathAction->handle();

        if (! $configPath) {
            throw new \RuntimeException('SSH config path not set');
        }

        $parseAction = app(ParseSshConfigAction::class);
        $fileConfigs = $parseAction->handle($configPath);
        $dbConfigs = SshConfig::all()->keyBy('host');

        $conflicts = [];
        $synced = [];

        // Sync from file to DB (add missing or update existing)
        foreach ($fileConfigs as $fileConfig) {
            $host = $fileConfig['host'];
            $existing = $dbConfigs->get($host);

            if ($existing) {
                // Update if different
                $fileProps = $fileConfig['properties'] ?? [];
                $dbProps = $existing->properties ?? [];

                if (json_encode($fileProps) !== json_encode($dbProps)) {
                    $existing->update([
                        'properties' => $fileProps,
                        'raw_block' => $fileConfig['raw_block'] ?? null,
                    ]);
                    $synced[] = ['host' => $host, 'action' => 'updated'];
                }
            } else {
                // Check if host already exists (unique constraint)
                try {
                    SshConfig::create([
                        'host' => $host,
                        'properties' => $fileConfig['properties'] ?? [],
                        'raw_block' => $fileConfig['raw_block'] ?? null,
                    ]);
                    $synced[] = ['host' => $host, 'action' => 'created'];
                } catch (\Illuminate\Database\QueryException $e) {
                    // Host already exists, add to conflicts
                    $conflicts[] = [
                        'host' => $host,
                        'source' => 'file',
                        'file_config' => $fileConfig,
                        'reason' => 'duplicate_host',
                    ];
                }
            }
        }

        // Add DB-only configs to file
        foreach ($dbConfigs as $host => $dbConfig) {
            $existsInFile = false;
            foreach ($fileConfigs as $fileConfig) {
                if ($fileConfig['host'] === $host) {
                    $existsInFile = true;
                    break;
                }
            }

            if (! $existsInFile) {
                $fileConfigs[] = [
                    'host' => $host,
                    'properties' => $dbConfig->properties ?? [],
                ];
                $synced[] = ['host' => $host, 'action' => 'added_to_file'];
            }
        }

        // Write updated configs back to file
        $writeAction = app(WriteSshConfigAction::class);
        $writeAction->handle($fileConfigs, $configPath);

        return [
            'synced' => $synced,
            'conflicts' => $conflicts,
        ];
    }
}
