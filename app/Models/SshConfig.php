<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SshConfig extends Model
{
    protected $fillable = [
        'host',
        'properties',
        'raw_block',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    /**
     * Convert model to form-compatible array.
     *
     * @return array{host: string, hostName: ?string, user: ?string, port: ?string, identityFile: ?string}
     */
    public function toFormData(): array
    {
        $properties = $this->properties ?? [];

        return [
            'host' => $this->host,
            'hostName' => $properties['HostName'] ?? null,
            'user' => $properties['User'] ?? null,
            'port' => $properties['Port'] ?? null,
            'identityFile' => $properties['IdentityFile'] ?? null,
        ];
    }

    /**
     * Transform form data to model attributes.
     *
     * @param  array{host: string, hostName: ?string, user: ?string, port: ?string, identityFile: ?string}  $data
     * @return array{host: string, properties: array<string, string>}
     */
    public static function fromFormData(array $data): array
    {
        $properties = array_filter([
            'HostName' => $data['hostName'] ?? null,
            'User' => $data['user'] ?? null,
            'Port' => $data['port'] ?? null,
            'IdentityFile' => $data['identityFile'] ?? null,
        ]);

        return [
            'host' => $data['host'],
            'properties' => $properties,
        ];
    }

    /**
     * Get the SSH command for this config.
     */
    public function getSshCommand(): string
    {
        return "ssh {$this->host}";
    }
}
