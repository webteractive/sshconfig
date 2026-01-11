<?php

use Illuminate\Support\Facades\File;
use App\Actions\ParseSshConfigAction;

it('returns empty array when file does not exist', function () {
    $action = new ParseSshConfigAction;

    expect($action->handle('/non/existent/path'))->toBe([]);
});

it('parses simple SSH config', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, "Host example\n    HostName example.com\n    User root\n");

    $action = new ParseSshConfigAction;
    $result = $action->handle($tempFile);

    expect($result)->toHaveCount(1)
        ->and($result[0]['host'])->toBe('example')
        ->and($result[0]['properties'])->toBe([
            'HostName' => 'example.com',
            'User' => 'root',
        ]);

    File::delete($tempFile);
});

it('parses multiple hosts', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, "Host host1\n    HostName host1.com\n\nHost host2\n    HostName host2.com\n");

    $action = new ParseSshConfigAction;
    $result = $action->handle($tempFile);

    expect($result)->toHaveCount(2)
        ->and($result[0]['host'])->toBe('host1')
        ->and($result[1]['host'])->toBe('host2');

    File::delete($tempFile);
});

it('ignores comments and empty lines', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, "# This is a comment\n\nHost example\n    HostName example.com\n");

    $action = new ParseSshConfigAction;
    $result = $action->handle($tempFile);

    expect($result)->toHaveCount(1)
        ->and($result[0]['host'])->toBe('example');

    File::delete($tempFile);
});

it('handles multiple values for same key', function () {
    $tempFile = sys_get_temp_dir().'/test_ssh_config_'.uniqid();
    File::put($tempFile, "Host example\n    IdentityFile ~/.ssh/id_rsa\n    IdentityFile ~/.ssh/id_ed25519\n");

    $action = new ParseSshConfigAction;
    $result = $action->handle($tempFile);

    expect($result[0]['properties']['IdentityFile'])->toBe([
        '~/.ssh/id_rsa',
        '~/.ssh/id_ed25519',
    ]);

    File::delete($tempFile);
});
