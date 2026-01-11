<?php

namespace App\Filament\Resources\SshConfigs;

use BackedEnum;
use App\Models\SshConfig;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Layout\Stack;
use App\Actions\SyncSshConfigToFileAction;
use App\Filament\Actions\DuplicateSshConfigAction;
use App\Filament\Resources\SshConfigs\Pages\ManageSshConfigs;

class SshConfigResource extends Resource
{
    protected static ?string $model = SshConfig::class;

    protected static ?string $title = 'Manage SSH Configs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('host')
                    ->label('Host')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->rules([
                        'required',
                        'string',
                        'max:255',
                        'regex:/^[a-zA-Z0-9._-]+$/',
                    ])
                    ->validationMessages([
                        'regex' => 'Host name can only contain letters, numbers, dots, underscores, and hyphens.',
                    ]),
                TextInput::make('hostName')
                    ->label('Host Name')
                    ->maxLength(255)
                    ->rules([
                        'nullable',
                        'string',
                        'max:255',
                    ]),
                TextInput::make('user')
                    ->label('User')
                    ->maxLength(255)
                    ->rules([
                        'nullable',
                        'string',
                        'max:255',
                    ]),
                TextInput::make('port')
                    ->label('Port')
                    ->numeric()
                    ->rules([
                        'nullable',
                        'integer',
                        'min:1',
                        'max:65535',
                    ])
                    ->validationMessages([
                        'min' => 'Port must be between 1 and 65535.',
                        'max' => 'Port must be between 1 and 65535.',
                    ]),
                TextInput::make('identityFile')
                    ->label('Identity File')
                    ->maxLength(255)
                    ->rules([
                        'nullable',
                        'string',
                        'max:255',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    TextColumn::make('host')
                        ->label('Host')
                        ->size(TextSize::Large)
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('properties.HostName')
                        ->label('Host Name')
                        ->icon('heroicon-o-globe-alt')
                        ->size(TextSize::Small)
                        ->searchable()
                        ->placeholder('—'),
                    TextColumn::make('properties.User')
                        ->label('User')
                        ->size(TextSize::Small)
                        ->icon('heroicon-o-user')
                        ->searchable()
                        ->placeholder('—'),
                    TextColumn::make('port')
                        ->label('Port')
                        ->size(TextSize::Small)
                        ->icon('heroicon-o-server')
                        ->searchable(),
                    TextColumn::make('properties.IdentityFile')
                        ->label('Identity File')
                        ->icon('heroicon-o-key')
                        ->searchable()
                        ->size(TextSize::Small)
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
                        ->tooltip('Click to copy and paste it to your favorite terminal app to connect to your server.')
                        ->fontFamily('mono')
                        ->badge()
                        ->color('slate')
                        ->size(TextSize::Large),
                    TextColumn::make('created_at')
                        ->label('Created')
                        ->icon('heroicon-o-calendar')
                        ->dateTime('M d, Y g:i A')
                        ->sortable()
                        ->color('gray')
                        ->size(TextSize::ExtraSmall),
                    TextColumn::make('updated_at')
                        ->label('Last Updated')
                        ->icon('heroicon-o-clock')
                        ->dateTime('M d, Y g:i A')
                        ->sortable()
                        ->color('gray')
                        ->size(TextSize::ExtraSmall),
                ])->space(1),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginationPageOptions([6, 9, 12, 60])
            ->defaultPaginationPageOption(6)
            ->recordActions([
                EditAction::make()
                    ->color('info')
                    ->fillForm(fn (SshConfig $record): array => $record->toFormData())
                    ->mutateFormDataUsing(fn (array $data): array => SshConfig::fromFormData($data))
                    ->after(fn () => self::syncToFile()),
                DuplicateSshConfigAction::make(),
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
