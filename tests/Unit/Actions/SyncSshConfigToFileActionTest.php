<?php

use App\Models\Setting;
use App\Models\SshConfig;
use Illuminate\Support\Facades\File;
use App\Actions\SyncSshConfigToFileAction;

it('throws exception when config path is not set', function () {
    $action = new SyncSshConfigToFileAction;

    expect(fn () => $action->handle())->toThrow(\RuntimeException::class, 'SSH config path not set');
});

it('writes configs to file', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    Setting::create([
        'key' => 'ssh_config_path',
        'value' => $tempFile,
    ]);

    SshConfig::factory()->create([
        'host' => 'test-host',
        'properties' => [
            'HostName' => 'example.com',
            'User' => 'root',
        ],
    ]);

    $action = new SyncSshConfigToFileAction;
    $action->handle();

    expect(File::exists($tempFile))->toBeTrue();

    $content = File::get($tempFile);
    expect($content)->toContain('Host test-host')
        ->and($content)->toContain('HostName example.com')
        ->and($content)->toContain('User root');

    File::delete($tempFile);
    Setting::where('key', 'ssh_config_path')->delete();
});

it('writes multiple configs to file', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    Setting::create([
        'key' => 'ssh_config_path',
        'value' => $tempFile,
    ]);

    SshConfig::factory()->create(['host' => 'host1', 'properties' => ['HostName' => 'host1.com']]);
    SshConfig::factory()->create(['host' => 'host2', 'properties' => ['HostName' => 'host2.com']]);

    $action = new SyncSshConfigToFileAction;
    $action->handle();

    $content = File::get($tempFile);
    expect($content)->toContain('Host host1')
        ->and($content)->toContain('Host host2');

    File::delete($tempFile);
    Setting::where('key', 'ssh_config_path')->delete();
});
