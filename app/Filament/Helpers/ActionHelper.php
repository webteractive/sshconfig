<?php

namespace App\Filament\Helpers;

use Filament\Actions\Action;

class ActionHelper
{
    /**
     * Reset the table if the action is on a page that has a table.
     */
    public static function resetTableIfNeeded(Action $action): void
    {
        $livewire = $action->getLivewire();
        if ($livewire && method_exists($livewire, 'resetTable')) {
            $livewire->resetTable();
        }
    }
}
