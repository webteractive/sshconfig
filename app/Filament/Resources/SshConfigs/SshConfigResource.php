<?php

namespace App\Filament\Resources\SshConfigs;

use BackedEnum;
use App\Models\SshConfig;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Layout\Stack;
use App\Actions\SyncSshConfigToFileAction;
use App\Filament\Resources\SshConfigs\Pages\ManageSshConfigs;

class SshConfigResource extends Resource
{
    protected static ?string $model = SshConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('host')
                    ->label('Host')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('hostName')
                    ->label('Host Name')
                    ->maxLength(255),
                TextInput::make('user')
                    ->label('User')
                    ->maxLength(255),
                TextInput::make('port')
                    ->label('Port')
                    ->numeric()
                    ->maxLength(5),
                TextInput::make('identityFile')
                    ->label('Identity File')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    TextColumn::make('host')
                        ->label('Host')
                        ->weight(FontWeight::Bold)
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('properties.HostName')
                        ->label('Host Name')
                        ->icon('heroicon-o-globe-alt')
                        ->searchable()
                        ->placeholder('—'),
                    TextColumn::make('properties.User')
                        ->label('User')
                        ->icon('heroicon-o-user')
                        ->searchable()
                        ->placeholder('—'),
                    TextColumn::make('properties.Port')
                        ->label('Port')
                        ->searchable()
                        ->placeholder('—'),
                    TextColumn::make('properties.IdentityFile')
                        ->label('Identity File')
                        ->icon('heroicon-o-key')
                        ->searchable()
                        ->placeholder('—')
                        ->wrap(),
                    TextColumn::make('ssh_command')
                        ->label('SSH Command')
                        ->icon('heroicon-o-command-line')
                        ->state(fn (SshConfig $record): string => $record->getSshCommand())
                        ->copyable()
                        ->copyableState(fn (SshConfig $record): string => $record->getSshCommand())
                        ->copyMessage('SSH command copied!')
                        ->copyMessageDuration(1500)
                        ->fontFamily('mono')
                        ->badge()
                        ->color('slate'),
                ])->space(1),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->defaultSort('host')
            ->paginationPageOptions([3, 6, 9, 12, 60])
            ->defaultPaginationPageOption(6)
            ->recordActions([
                EditAction::make()
                    ->fillForm(fn (SshConfig $record): array => $record->toFormData())
                    ->mutateFormDataUsing(fn (array $data): array => SshConfig::fromFormData($data))
                    ->after(fn () => self::syncToFile()),
                DeleteAction::make()
                    ->after(fn () => self::syncToFile()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function syncToFile(): void
    {
        rescue(fn () => app(SyncSshConfigToFileAction::class)->handle());
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSshConfigs::route('/'),
        ];
    }
}
