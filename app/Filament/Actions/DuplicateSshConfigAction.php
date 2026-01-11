<?php

namespace App\Filament\Actions;

use App\Models\SshConfig;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Actions\SyncSshConfigToFileAction;

class DuplicateSshConfigAction
{
    public static function make(): Action
    {
        return Action::make('duplicate')
            ->label('Duplicate')
            ->icon('heroicon-o-square-2-stack')
            ->color('warning')
            ->action(function (SshConfig $record): void {
                // Generate a unique host name
                $originalHost = $record->host;
                $newHost = $originalHost;
                $counter = 1;

                // Find a unique host name
                while (SshConfig::where('host', $newHost)->exists()) {
                    $newHost = $originalHost.'-copy-'.$counter;
                    $counter++;
                }

                // Create duplicate
                $duplicate = $record->replicate();
                $duplicate->host = $newHost;
                $duplicate->save();

                // Sync to file
                try {
                    $syncAction = app(SyncSshConfigToFileAction::class);
                    $syncAction->handle();

                    Notification::make()
                        ->title('Duplicate Successful')
                        ->body("SSH configuration has been duplicated successfully as '{$newHost}'.")
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Duplicate Successful with Warnings')
                        ->body("SSH configuration has been duplicated as '{$newHost}', but the sync to file operation failed: ".$e->getMessage())
                        ->warning()
                        ->send();
                }
            });
    }
}
