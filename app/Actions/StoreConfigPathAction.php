<?php

namespace App\Actions;

use App\Models\Setting;

class StoreConfigPathAction
{
    public function handle(string $configPath): void
    {
        Setting::updateOrCreate(
            ['key' => 'ssh_config_path'],
            ['value' => $configPath]
        );
    }
}
