<?php

namespace App\Filament\Resources\SshConfigs\Pages;

use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use App\Actions\GetConfigPathAction;
use App\Filament\Actions\SyncBothAction;
use App\Actions\SyncSshConfigToFileAction;
use App\Filament\Actions\SyncToFileAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Actions\SyncFromFileAction;
use App\Filament\Actions\SetConfigPathAction;
use App\Filament\Resources\SshConfigs\SshConfigResource;

class ManageSshConfigs extends ManageRecords
{
    protected static string $resource = SshConfigResource::class;

    protected static ?string $title = 'Manage SSH Configs';

    public function mount(): void
    {
        $getPathAction = app(GetConfigPathAction::class);
        $path = $getPathAction->handle();

        if (empty($path)) {
            $this->defaultAction = 'setConfigPath';
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            SetConfigPathAction::make(),
            ActionGroup::make([
                SyncFromFileAction::make(),
                SyncToFileAction::make(),
                SyncBothAction::make(),
            ])
                ->label('Sync')
                ->icon('heroicon-o-arrows-right-left')
                ->button()
                ->outlined()
                ->color('primary'),
            CreateAction::make()
                ->label('New')
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
}
