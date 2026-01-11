<?php

use App\Models\SshConfig;

it('can convert model to form data', function () {
    $config = SshConfig::factory()->create([
        'host' => 'test-host',
        'properties' => [
            'HostName' => 'example.com',
            'User' => 'root',
            'Port' => '2222',
            'IdentityFile' => '~/.ssh/id_rsa',
        ],
    ]);

    $formData = $config->toFormData();

    expect($formData)->toBe([
        'host' => 'test-host',
        'hostName' => 'example.com',
        'user' => 'root',
        'port' => '2222',
        'identityFile' => '~/.ssh/id_rsa',
    ]);
});

it('handles missing properties in form data conversion', function () {
    $config = SshConfig::factory()->create([
        'host' => 'test-host',
        'properties' => [],
    ]);

    $formData = $config->toFormData();

    expect($formData)->toBe([
        'host' => 'test-host',
        'hostName' => null,
        'user' => null,
        'port' => null,
        'identityFile' => null,
    ]);
});

it('can convert form data to model attributes', function () {
    $formData = [
        'host' => 'test-host',
        'hostName' => 'example.com',
        'user' => 'root',
        'port' => '2222',
        'identityFile' => '~/.ssh/id_rsa',
    ];

    $attributes = SshConfig::fromFormData($formData);

    expect($attributes)->toBe([
        'host' => 'test-host',
        'properties' => [
            'HostName' => 'example.com',
            'User' => 'root',
            'Port' => '2222',
            'IdentityFile' => '~/.ssh/id_rsa',
        ],
    ]);
});

it('filters out null values in form data conversion', function () {
    $formData = [
        'host' => 'test-host',
        'hostName' => null,
        'user' => 'root',
        'port' => null,
        'identityFile' => null,
    ];

    $attributes = SshConfig::fromFormData($formData);

    expect($attributes)->toBe([
        'host' => 'test-host',
        'properties' => [
            'User' => 'root',
        ],
    ]);
});

it('generates correct SSH command', function () {
    $config = SshConfig::factory()->create([
        'host' => 'test-host',
    ]);

    expect($config->getSshCommand())->toBe('ssh test-host');
});

it('returns default port when port is not set', function () {
    $config = SshConfig::factory()->create([
        'host' => 'test-host',
        'properties' => [],
    ]);

    expect($config->port)->toBe('22');
});

it('returns configured port when set', function () {
    $config = SshConfig::factory()->create([
        'host' => 'test-host',
        'properties' => [
            'Port' => '2222',
        ],
    ]);

    expect($config->port)->toBe('2222');
});
