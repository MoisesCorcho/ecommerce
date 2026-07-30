<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\AddressesRelationManager;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigation.groups.accounts');
    }

    public static function getModelLabel(): string
    {
        return __('users.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('users.model.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('users.section.account_details'))
                    ->description(__('users.section.account_details_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('users.fields.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                TextInput::make('last_name')
                                    ->label(__('users.fields.last_name'))
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                TextInput::make('email')
                                    ->label(__('users.fields.email'))
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(1),
                                TextInput::make('phone')
                                    ->label(__('users.fields.phone'))
                                    ->tel()
                                    ->placeholder(__('users.placeholders.phone'))
                                    ->maxLength(32)
                                    ->helperText(__('users.helpers.phone_optional'))
                                    ->columnSpan(1),
                                TextInput::make('password')
                                    ->label(__('users.fields.password'))
                                    ->password()
                                    ->revealable()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                    ->maxLength(255)
                                    ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                        ? __('users.helpers.password_keep')
                                        : null)
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('users.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label(__('users.fields.last_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('users.fields.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->label(__('users.fields.phone'))
                    ->placeholder(__('users.placeholders.empty'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('addresses_count')
                    ->counts('addresses')
                    ->label(__('users.fields.addresses_count'))
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('users.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('users.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->label(__('users.actions.edit')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('users.actions.delete_selected'))
                        ->requiresConfirmation()
                        ->modalHeading(__('users.modals.delete_bulk_heading'))
                        ->modalDescription(__('users.modals.delete_bulk_description'))
                        ->modalSubmitActionLabel(__('users.actions.confirm_delete')),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedUsers)
            ->emptyStateHeading(__('users.empty.heading'))
            ->emptyStateDescription(__('users.empty.description'))
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('users.actions.create'))
                    ->icon(Heroicon::Plus),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
