<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use App\Actions\GetConfigPathAction;
use App\Filament\Helpers\ActionHelper;
use Filament\Notifications\Notification;
use App\Actions\SyncSshConfigFromFileAction;

class SyncFromFileAction
{
    public static function make(): Action
    {
        return Action::make('syncFromFile')
            ->label('Sync From File')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('info')
            ->action(function (Action $action): void {
                $getPathAction = app(GetConfigPathAction::class);
                $path = $getPathAction->handle();

                if (! $path) {
                    Notification::make()
                        ->title('Sync Failed')
                        ->body('Unable to sync: SSH config path has not been set. Please set the config path first.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    $syncAction = app(SyncSshConfigFromFileAction::class);
                    $syncAction->handle($path);

                    ActionHelper::resetTableIfNeeded($action);

                    Notification::make()
                        ->title('Sync Successful')
                        ->body('SSH configurations have been synced from the file successfully.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Sync Failed')
                        ->body('Failed to sync SSH configurations from file: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
