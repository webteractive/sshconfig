<?php

namespace App\Actions;

use App\Models\Setting;

class GetConfigPathAction
{
    public function handle(): ?string
    {
        $setting = Setting::where('key', 'ssh_config_path')->first();

        return $setting?->value;
    }
}
