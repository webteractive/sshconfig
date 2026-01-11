<?php

use App\Models\Setting;
use App\Actions\GetConfigPathAction;

it('returns null when config path is not set', function () {
    $action = new GetConfigPathAction;

    expect($action->handle())->toBeNull();
});

it('returns config path when set', function () {
    Setting::create([
        'key' => 'ssh_config_path',
        'value' => '/path/to/config',
    ]);

    $action = new GetConfigPathAction;

    expect($action->handle())->toBe('/path/to/config');
});
