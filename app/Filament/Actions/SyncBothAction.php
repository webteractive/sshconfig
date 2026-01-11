<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use App\Filament\Helpers\ActionHelper;
use App\Actions\SyncSshConfigBothAction;
use Filament\Notifications\Notification;

class SyncBothAction
{
    public static function make(): Action
    {
        return Action::make('syncBoth')
            ->label('Sync Both')
            ->icon('heroicon-o-arrows-right-left')
            ->color('warning')
            ->action(function (Action $action): void {
                try {
                    $syncAction = app(SyncSshConfigBothAction::class);
                    $result = $syncAction->handle();

                    ActionHelper::resetTableIfNeeded($action);

                    $syncedCount = count($result['synced'] ?? []);
                    $conflictsCount = count($result['conflicts'] ?? []);

                    $message = "Successfully synced {$syncedCount} SSH configuration".($syncedCount !== 1 ? 's' : '').'.';
                    if ($conflictsCount > 0) {
                        $message .= " Found {$conflictsCount} conflict".($conflictsCount !== 1 ? 's' : '').' that require resolution.';
                    }

                    Notification::make()
                        ->title('Sync Successful')
                        ->body($message)
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Sync Failed')
                        ->body('Failed to sync SSH configurations: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
