<?php

namespace App\Actions;

use Illuminate\Support\Facades\File;

class ParseSshConfigAction
{
    public function handle(string $configPath): array
    {
        if (! File::exists($configPath)) {
            return [];
        }

        $content = File::get($configPath);
        $lines = explode("\n", $content);
        $configs = [];
        $currentHost = null;
        $currentBlock = [];
        $currentRawBlock = [];
        $inHostBlock = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines and comments
            if (empty($trimmed) || str_starts_with($trimmed, '#')) {
                $currentRawBlock[] = $line;

                continue;
            }

            // Check if this is a Host directive
            if (preg_match('/^Host\s+(.+)$/i', $trimmed, $matches)) {
                // Save previous host block if exists
                if ($inHostBlock && $currentHost !== null) {
                    $configs[] = [
                        'host' => $currentHost,
                        'properties' => $currentBlock,
                        'raw_block' => implode("\n", $currentRawBlock),
                    ];
                }

                // Start new host block
                $currentHost = trim($matches[1]);
                $currentBlock = [];
                $currentRawBlock = [$line];
                $inHostBlock = true;
            } elseif ($inHostBlock) {
                // Parse property line (e.g., "HostName example.com", "User root")
                if (preg_match('/^(\S+)\s+(.+)$/', $trimmed, $propMatches)) {
                    $key = $propMatches[1];
                    $value = trim($propMatches[2]);

                    // Handle multiple values for the same key
                    if (isset($currentBlock[$key])) {
                        if (! is_array($currentBlock[$key])) {
                            $currentBlock[$key] = [$currentBlock[$key]];
                        }
                        $currentBlock[$key][] = $value;
                    } else {
                        $currentBlock[$key] = $value;
                    }
                }

                $currentRawBlock[] = $line;
            } else {
                $currentRawBlock[] = $line;
            }
        }

        // Save last host block
        if ($inHostBlock && $currentHost !== null) {
            $configs[] = [
                'host' => $currentHost,
                'properties' => $currentBlock,
                'raw_block' => implode("\n", $currentRawBlock),
            ];
        }

        return $configs;
    }
}
