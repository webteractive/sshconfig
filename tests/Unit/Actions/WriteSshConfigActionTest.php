<?php

use Illuminate\Support\Facades\File;
use App\Actions\WriteSshConfigAction;

it('writes SSH config to file', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    $configs = [
        [
            'host' => 'example',
            'properties' => [
                'HostName' => 'example.com',
                'User' => 'root',
            ],
        ],
    ];

    $action = new WriteSshConfigAction;
    $action->handle($configs, $tempFile);

    expect(File::exists($tempFile))->toBeTrue();

    $content = File::get($tempFile);
    expect($content)->toContain('Host example')
        ->and($content)->toContain('HostName example.com')
        ->and($content)->toContain('User root');

    File::delete($tempFile);
});

it('creates directory if it does not exist', function () {
    $tempDir = sys_get_temp_dir().'/test_ssh_dir_'.uniqid();
    $tempFile = $tempDir.'/config';

    $configs = [
        [
            'host' => 'example',
            'properties' => [],
        ],
    ];

    $action = new WriteSshConfigAction;
    $action->handle($configs, $tempFile);

    expect(File::isDirectory($tempDir))->toBeTrue()
        ->and(File::exists($tempFile))->toBeTrue();

    File::deleteDirectory($tempDir);
});

it('handles multiple configs', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    $configs = [
        [
            'host' => 'host1',
            'properties' => ['HostName' => 'host1.com'],
        ],
        [
            'host' => 'host2',
            'properties' => ['HostName' => 'host2.com'],
        ],
    ];

    $action = new WriteSshConfigAction;
    $action->handle($configs, $tempFile);

    $content = File::get($tempFile);
    expect($content)->toContain('Host host1')
        ->and($content)->toContain('Host host2');

    File::delete($tempFile);
});

it('handles array values for properties', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    $configs = [
        [
            'host' => 'example',
            'properties' => [
                'IdentityFile' => ['~/.ssh/id_rsa', '~/.ssh/id_ed25519'],
            ],
        ],
    ];

    $action = new WriteSshConfigAction;
    $action->handle($configs, $tempFile);

    $content = File::get($tempFile);
    expect($content)->toContain('IdentityFile ~/.ssh/id_rsa')
        ->and($content)->toContain('IdentityFile ~/.ssh/id_ed25519');

    File::delete($tempFile);
});
