<?php

namespace App\Actions;

use Illuminate\Support\Facades\File;

class WriteSshConfigAction
{
    public function handle(array $configs, string $configPath): void
    {
        $lines = [];

        foreach ($configs as $config) {
            $host = $config['host'] ?? $config['host'];
            $properties = $config['properties'] ?? [];

            // Write Host line
            $lines[] = "Host {$host}";

            // Write properties
            foreach ($properties as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $lines[] = "    {$key} {$val}";
                    }
                } else {
                    $lines[] = "    {$key} {$value}";
                }
            }

            // Add blank line between hosts
            $lines[] = '';
        }

        // Ensure directory exists
        $directory = dirname($configPath);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($configPath, implode("\n", $lines));
    }
}
