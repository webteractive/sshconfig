<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use App\Actions\GetConfigPathAction;
use App\Actions\StoreConfigPathAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use App\Actions\CreateSshConfigBackupAction;
use App\Actions\SyncSshConfigFromFileAction;

class SetConfigPathAction
{
    public static function make(): Action
    {
        return Action::make('setConfigPath')
            ->label('Set SSH Config Path')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('gray')
            ->modalHeading('SSH Config Path')
            ->modalDescription('Enter the path to your SSH config file')
            ->form([
                Placeholder::make('important_notice')
                    ->hiddenLabel()
                    ->color('warning')
                    ->content('Setting your SSH config path is required to use this application. This path tells the app where your SSH configuration file is located on your system. Once set, you will be able to manage and sync your SSH configurations.'),
                TextInput::make('configPath')
                    ->label('SSH Config Path')
                    ->required()
                    ->placeholder('~/.ssh/config')
                    ->helperText('Enter the absolute path to your SSH config file')
                    ->rules([
                        'required',
                        'string',
                        function ($attribute, $value, $fail) {
                            if (empty($value)) {
                                return;
                            }

                            $configPath = trim($value);

                            // Validate that it's an absolute path
                            if (! str_starts_with($configPath, '/')) {
                                $fail('Config path must be an absolute path (starting with /).');

                                return;
                            }

                            // Check if directory exists (file may not exist yet)
                            $directory = dirname($configPath);
                            if (! is_dir($directory)) {
                                $fail("Directory does not exist: {$directory}");
                            }
                        },
                    ]),
            ])
            ->action(function (array $data): void {
                $configPath = trim($data['configPath']);

                $storeAction = app(StoreConfigPathAction::class);
                $storeAction->handle($configPath);

                // Create backup if file exists
                if (file_exists($configPath)) {
                    try {
                        $backupAction = app(CreateSshConfigBackupAction::class);
                        $backupPath = $backupAction->handle($configPath);

                        // Sync from file to DB
                        $syncAction = app(SyncSshConfigFromFileAction::class);
                        $syncAction->handle($configPath);

                        Notification::make()
                            ->title('SSH Config Path Saved')
                            ->body("The SSH config path has been saved successfully. A backup was created at: {$backupPath}")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('SSH Config Path Saved with Warnings')
                            ->body('The SSH config path has been saved, but the backup or sync operation failed: '.$e->getMessage())
                            ->warning()
                            ->send();
                    }
                } else {
                    Notification::make()
                        ->title('SSH Config Path Saved')
                        ->body('The SSH config path has been saved successfully. The file will be created automatically on the first sync.')
                        ->success()
                        ->send();
                }
            })
            ->visible(function (): bool {
                $getPathAction = app(GetConfigPathAction::class);
                $path = $getPathAction->handle();

                return empty($path);
            })
            ->modalCancelAction(false)
            ->closeModalByClickingAway(false);
    }
}
