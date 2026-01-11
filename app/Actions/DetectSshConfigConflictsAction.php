<?php

namespace App\Actions;

use App\Models\SshConfig;

class DetectSshConfigConflictsAction
{
    public function handle(): array
    {
        $getPathAction = app(GetConfigPathAction::class);
        $configPath = $getPathAction->handle();

        if (! $configPath) {
            return [];
        }

        $parseAction = app(ParseSshConfigAction::class);
        $fileConfigs = $parseAction->handle($configPath);
        $dbConfigs = SshConfig::all()->keyBy('host');

        $conflicts = [];

        foreach ($fileConfigs as $fileConfig) {
            $host = $fileConfig['host'];
            $dbConfig = $dbConfigs->get($host);

            if ($dbConfig) {
                // Check if properties differ
                $fileProps = $fileConfig['properties'] ?? [];
                $dbProps = $dbConfig->properties ?? [];

                if (json_encode($fileProps) !== json_encode($dbProps)) {
                    $conflicts[] = [
                        'host' => $host,
                        'source' => 'both',
                        'file_config' => $fileConfig,
                        'db_config' => $dbConfig->toArray(),
                    ];
                }
            }
        }

        // Check for DB-only configs
        foreach ($dbConfigs as $host => $dbConfig) {
            $existsInFile = false;
            foreach ($fileConfigs as $fileConfig) {
                if ($fileConfig['host'] === $host) {
                    $existsInFile = true;
                    break;
                }
            }

            if (! $existsInFile) {
                $conflicts[] = [
                    'host' => $host,
                    'source' => 'database',
                    'db_config' => $dbConfig->toArray(),
                ];
            }
        }

        // Check for file-only configs
        foreach ($fileConfigs as $fileConfig) {
            $host = $fileConfig['host'];
            if (! $dbConfigs->has($host)) {
                $conflicts[] = [
                    'host' => $host,
                    'source' => 'file',
                    'file_config' => $fileConfig,
                ];
            }
        }

        return $conflicts;
    }
}
