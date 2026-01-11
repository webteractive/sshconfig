<?php

use App\Models\Setting;
use App\Actions\StoreConfigPathAction;

it('stores config path', function () {
    $action = new StoreConfigPathAction;
    $action->handle('/path/to/config');

    $setting = Setting::where('key', 'ssh_config_path')->first();

    expect($setting)->not->toBeNull()
        ->and($setting->value)->toBe('/path/to/config');
});

it('updates existing config path', function () {
    Setting::create([
        'key' => 'ssh_config_path',
        'value' => '/old/path',
    ]);

    $action = new StoreConfigPathAction;
    $action->handle('/new/path');

    $setting = Setting::where('key', 'ssh_config_path')->first();

    expect($setting->value)->toBe('/new/path')
        ->and(Setting::where('key', 'ssh_config_path')->count())->toBe(1);
});
