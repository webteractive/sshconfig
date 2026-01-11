<?php

namespace App\Filament\Resources\SshConfigs\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use App\Actions\GetConfigPathAction;
use App\Actions\StoreConfigPathAction;
use App\Actions\SyncSshConfigBothAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Actions\SyncSshConfigToFileAction;
use Filament\Resources\Pages\ManageRecords;
use App\Actions\CreateSshConfigBackupAction;
use App\Actions\SyncSshConfigFromFileAction;
use App\Filament\Resources\SshConfigs\SshConfigResource;

class ManageSshConfigs extends ManageRecords
{
    protected static string $resource = SshConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSetConfigPathAction(),
            ActionGroup::make([
                $this->getSyncFromFileAction(),
                $this->getSyncToFileAction(),
                $this->getSyncBothAction(),
            ])
                ->label('Sync')
                ->icon('heroicon-o-arrows-right-left')
                ->button()
                ->color('gray'),
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $properties = [];
                    if (! empty($data['hostName'])) {
                        $properties['HostName'] = $data['hostName'];
                    }
                    if (! empty($data['user'])) {
                        $properties['User'] = $data['user'];
                    }
                    if (! empty($data['port'])) {
                        $properties['Port'] = $data['port'];
                    }
                    if (! empty($data['identityFile'])) {
                        $properties['IdentityFile'] = $data['identityFile'];
                    }

                    return [
                        'host' => $data['host'],
                        'properties' => $properties,
                    ];
                })
                ->after(function (): void {
                    try {
                        $syncAction = app(SyncSshConfigToFileAction::class);
                        $syncAction->handle();
                    } catch (\Exception $e) {
                        // Silently fail - sync errors can be handled separately
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;
        if ($record) {
            $properties = $record->properties ?? [];
            $data['hostName'] = $properties['HostName'] ?? null;
            $data['user'] = $properties['User'] ?? null;
            $data['port'] = $properties['Port'] ?? null;
            $data['identityFile'] = $properties['IdentityFile'] ?? null;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $properties = [];
        if (! empty($data['hostName'])) {
            $properties['HostName'] = $data['hostName'];
        }
        if (! empty($data['user'])) {
            $properties['User'] = $data['user'];
        }
        if (! empty($data['port'])) {
            $properties['Port'] = $data['port'];
        }
        if (! empty($data['identityFile'])) {
            $properties['IdentityFile'] = $data['identityFile'];
        }

        $data['properties'] = $properties;
        unset($data['hostName'], $data['user'], $data['port'], $data['identityFile']);

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            $syncAction = app(SyncSshConfigToFileAction::class);
            $syncAction->handle();
        } catch (\Exception $e) {
            // Silently fail - sync errors can be handled separately
        }
    }

    protected function afterSave(): void
    {
        try {
            $syncAction = app(SyncSshConfigToFileAction::class);
            $syncAction->handle();
        } catch (\Exception $e) {
            // Silently fail - sync errors can be handled separately
        }
    }

    protected function getSetConfigPathAction(): Action
    {
        return Action::make('setConfigPath')
            ->label('Set SSH Config Path')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('gray')
            ->modalHeading('SSH Config Path')
            ->modalDescription('Enter the path to your SSH config file')
            ->form([
                TextInput::make('configPath')
                    ->label('SSH Config Path')
                    ->required()
                    ->placeholder('/Users/username/.ssh/config')
                    ->helperText('Enter the absolute path to your SSH config file'),
            ])
            ->action(function (array $data): void {
                $configPath = trim($data['configPath']);

                // Validate that it's an absolute path
                if (! str_starts_with($configPath, '/')) {
                    Notification::make()
                        ->title('Error')
                        ->body('Config path must be an absolute path (starting with /).')
                        ->danger()
                        ->send();

                    return;
                }

                // Check if directory exists (file may not exist yet)
                $directory = dirname($configPath);
                if (! is_dir($directory)) {
                    Notification::make()
                        ->title('Error')
                        ->body("Directory does not exist: {$directory}")
                        ->danger()
                        ->send();

                    return;
                }

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
                            ->title('Success')
                            ->body("Config path saved. Backup created at: {$backupPath}")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Warning')
                            ->body('Config path saved but backup/sync failed: '.$e->getMessage())
                            ->warning()
                            ->send();
                    }
                } else {
                    Notification::make()
                        ->title('Success')
                        ->body('Config path saved. File will be created on first sync.')
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

    protected function getSyncFromFileAction(): Action
    {
        return Action::make('syncFromFile')
            ->label('Sync From File')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('info')
            ->action(function (): void {
                $getPathAction = app(GetConfigPathAction::class);
                $path = $getPathAction->handle();

                if (! $path) {
                    Notification::make()
                        ->title('Error')
                        ->body('SSH config path not set.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    $syncAction = app(SyncSshConfigFromFileAction::class);
                    $syncAction->handle($path);

                    $this->resetTable();

                    Notification::make()
                        ->title('Synced')
                        ->body('SSH configs synced from file successfully.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error')
                        ->body('Failed to sync from file: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected function getSyncToFileAction(): Action
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
                        ->title('Synced')
                        ->body('SSH configs synced to file successfully.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error')
                        ->body('Failed to sync to file: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected function getSyncBothAction(): Action
    {
        return Action::make('syncBoth')
            ->label('Sync Both')
            ->icon('heroicon-o-arrows-right-left')
            ->color('warning')
            ->action(function (): void {
                try {
                    $syncAction = app(SyncSshConfigBothAction::class);
                    $result = $syncAction->handle();

                    $this->resetTable();

                    $syncedCount = count($result['synced'] ?? []);
                    $conflictsCount = count($result['conflicts'] ?? []);

                    $message = "Synced {$syncedCount} config".($syncedCount !== 1 ? 's' : '').'.';
                    if ($conflictsCount > 0) {
                        $message .= " Found {$conflictsCount} conflict".($conflictsCount !== 1 ? 's' : '').' that need resolution.';
                    }

                    Notification::make()
                        ->title('Synced')
                        ->body($message)
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error')
                        ->body('Failed to sync: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
