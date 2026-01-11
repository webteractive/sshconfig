<?php

namespace App\Actions;

use App\Models\SshConfig;

class ResolveSshConfigConflictAction
{
    public function handle(string $host, string $newHost, bool $updateExisting = false): void
    {
        $existing = SshConfig::where('host', $host)->first();

        if (! $existing) {
            throw new \RuntimeException("Host '{$host}' not found");
        }

        if ($updateExisting) {
            // Update existing entry with new host name
            $existing->update(['host' => $newHost]);
        } else {
            // Create new entry with new host name, keep old one
            $existing->replicate()->fill(['host' => $newHost])->save();
        }
    }
}
