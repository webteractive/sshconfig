<?php

use App\Models\SshConfig;
use Illuminate\Support\Facades\File;
use App\Actions\SyncSshConfigFromFileAction;

it('creates new configs from file', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, "Host test-host\n    HostName example.com\n    User root\n");

    $action = new SyncSshConfigFromFileAction;
    $action->handle($tempFile);

    $config = SshConfig::where('host', 'test-host')->first();

    expect($config)->not->toBeNull()
        ->and($config->properties['HostName'])->toBe('example.com')
        ->and($config->properties['User'])->toBe('root');

    File::delete($tempFile);
});

it('updates existing configs from file', function () {
    $config = SshConfig::factory()->create([
        'host' => 'test-host',
        'properties' => ['HostName' => 'old.com'],
    ]);

    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, "Host test-host\n    HostName new.com\n    User root\n");

    $action = new SyncSshConfigFromFileAction;
    $action->handle($tempFile);

    $config->refresh();

    expect($config->properties['HostName'])->toBe('new.com')
        ->and($config->properties['User'])->toBe('root');

    File::delete($tempFile);
});

it('handles empty file', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, '');

    $action = new SyncSshConfigFromFileAction;
    $action->handle($tempFile);

    // Should not throw an error
    expect(true)->toBeTrue();

    File::delete($tempFile);
});
