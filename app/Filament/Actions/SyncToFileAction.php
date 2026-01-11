<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Actions\SyncSshConfigToFileAction;

class SyncToFileAction
{
    public static function make(): Action
    {
        return Action::make('syncToFile')
            ->label('Sync To File')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->action(function (): void {
                try {
                    $syncAction = app(SyncSshConfigToFileAction::class);
                    $syncAction->handle();

                    Notification::make()
                        ->title('Sync Successful')
                        ->body('SSH configurations have been synced to the file successfully.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Sync Failed')
                        ->body('Failed to sync SSH configurations to file: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
