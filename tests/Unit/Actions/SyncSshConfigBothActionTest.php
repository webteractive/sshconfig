<?php

use App\Models\Setting;
use App\Models\SshConfig;
use Illuminate\Support\Facades\File;
use App\Actions\SyncSshConfigBothAction;

it('throws exception when config path is not set', function () {
    $action = new SyncSshConfigBothAction;

    expect(fn () => $action->handle())->toThrow(\RuntimeException::class, 'SSH config path not set');
});

it('syncs configs from file to database', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, "Host file-host\n    HostName file.com\n    User root\n");

    Setting::create([
        'key' => 'ssh_config_path',
        'value' => $tempFile,
    ]);

    $action = new SyncSshConfigBothAction;
    $result = $action->handle();

    $config = SshConfig::where('host', 'file-host')->first();

    expect($config)->not->toBeNull()
        ->and($result['synced'])->not->toBeEmpty();

    File::delete($tempFile);
    Setting::where('key', 'ssh_config_path')->delete();
});

it('syncs configs from database to file', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, '');

    Setting::create([
        'key' => 'ssh_config_path',
        'value' => $tempFile,
    ]);

    SshConfig::factory()->create([
        'host' => 'db-host',
        'properties' => ['HostName' => 'db.com'],
    ]);

    $action = new SyncSshConfigBothAction;
    $result = $action->handle();

    $content = File::get($tempFile);
    expect($content)->toContain('Host db-host')
        ->and($result['synced'])->not->toBeEmpty();

    File::delete($tempFile);
    Setting::where('key', 'ssh_config_path')->delete();
});

it('updates existing configs when properties differ', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, "Host test-host\n    HostName new.com\n");

    Setting::create([
        'key' => 'ssh_config_path',
        'value' => $tempFile,
    ]);

    $config = SshConfig::factory()->create([
        'host' => 'test-host',
        'properties' => ['HostName' => 'old.com'],
    ]);

    $action = new SyncSshConfigBothAction;
    $action->handle();

    $config->refresh();

    expect($config->properties['HostName'])->toBe('new.com');

    File::delete($tempFile);
    Setting::where('key', 'ssh_config_path')->delete();
});

it('reloads database configs after syncing from file', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, "Host file-host\n    HostName file.com\n");

    Setting::create([
        'key' => 'ssh_config_path',
        'value' => $tempFile,
    ]);

    SshConfig::factory()->create([
        'host' => 'db-host',
        'properties' => ['HostName' => 'db.com'],
    ]);

    $action = new SyncSshConfigBothAction;
    $result = $action->handle();

    // Should sync both file-host (from file) and db-host (from DB)
    $syncedHosts = collect($result['synced'])->pluck('host')->toArray();

    expect($syncedHosts)->toContain('file-host')
        ->and($syncedHosts)->toContain('db-host');

    File::delete($tempFile);
    Setting::where('key', 'ssh_config_path')->delete();
});
