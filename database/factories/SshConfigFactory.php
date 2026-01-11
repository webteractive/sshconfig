<?php

namespace Database\Factories;

use App\Models\SshConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SshConfig>
 */
class SshConfigFactory extends Factory
{
    protected $model = SshConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'host' => fake()->unique()->domainWord(),
            'properties' => [
                'HostName' => fake()->domainName(),
                'User' => fake()->userName(),
                'Port' => (string) fake()->numberBetween(22, 65535),
                'IdentityFile' => '~/.ssh/id_rsa',
            ],
            'raw_block' => null,
        ];
    }
}
