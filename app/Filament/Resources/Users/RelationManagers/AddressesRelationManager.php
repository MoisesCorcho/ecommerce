<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Actions\Addresses\CreateAddressAction;
use App\Actions\Addresses\DeleteAddressAction;
use App\Actions\Addresses\UpdateAddressAction;
use App\DTOs\Addresses\UpsertAddressDTO;
use App\Models\Address;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedMapPin;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('addresses.relation.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('addresses.section.shipping'))
                    ->description(__('addresses.section.shipping_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label(__('addresses.fields.label'))
                                    ->placeholder(__('addresses.placeholders.label'))
                                    ->maxLength(64)
                                    ->columnSpan(1),
                                Toggle::make('is_default')
                                    ->label(__('addresses.fields.is_default'))
                                    ->helperText(__('addresses.helpers.is_default'))
                                    ->default(false)
                                    ->inline(false)
                                    ->columnSpan(1),
                                TextInput::make('full_name')
                                    ->label(__('addresses.fields.full_name'))
                                    ->placeholder(__('addresses.placeholders.full_name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                TextInput::make('phone')
                                    ->label(__('addresses.fields.phone'))
                                    ->tel()
                                    ->placeholder(__('addresses.placeholders.phone'))
                                    ->required()
                                    ->maxLength(32)
                                    ->columnSpan(1),
                                TextInput::make('address_line_1')
                                    ->label(__('addresses.fields.address_line_1'))
                                    ->placeholder(__('addresses.placeholders.address_line_1'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('address_line_2')
                                    ->label(__('addresses.fields.address_line_2'))
                                    ->placeholder(__('addresses.placeholders.address_line_2'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('city')
                                    ->label(__('addresses.fields.city'))
                                    ->placeholder(__('addresses.placeholders.city'))
                                    ->required()
                                    ->maxLength(120)
                                    ->columnSpan(1),
                                TextInput::make('state')
                                    ->label(__('addresses.fields.state'))
                                    ->placeholder(__('addresses.placeholders.state'))
                                    ->required()
                                    ->maxLength(120)
                                    ->columnSpan(1),
                                TextInput::make('country')
                                    ->label(__('addresses.fields.country'))
                                    ->placeholder(__('addresses.placeholders.country'))
                                    ->required()
                                    ->length(2)
                                    ->default('CO')
                                    ->helperText(__('addresses.helpers.country'))
                                    ->maxLength(2)
                                    ->columnSpan(1),
                                TextInput::make('postal_code')
                                    ->label(__('addresses.fields.postal_code'))
                                    ->placeholder(__('addresses.placeholders.postal_code'))
                                    ->maxLength(32)
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->modelLabel(__('addresses.model.label'))
            ->pluralModelLabel(__('addresses.model.plural'))
            ->columns([
                TextColumn::make('label')
                    ->label(__('addresses.fields.label'))
                    ->placeholder(__('addresses.placeholders.empty'))
                    ->toggleable(),
                TextColumn::make('full_name')
                    ->label(__('addresses.fields.full_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('addresses.fields.phone'))
                    ->searchable(),
                TextColumn::make('city')
                    ->label(__('addresses.fields.city'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('state')
                    ->label(__('addresses.fields.state_short'))
                    ->toggleable(),
                TextColumn::make('country')
                    ->label(__('addresses.fields.country_short'))
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_default')
                    ->label(__('addresses.fields.is_default_short'))
                    ->boolean(),
                TextColumn::make('address_line_1')
                    ->label(__('addresses.fields.address'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('addresses.actions.create'))
                    ->icon(Heroicon::Plus)
                    ->using(function (array $data, string $model): Model {
                        /** @var User $owner */
                        $owner = $this->getOwnerRecord();

                        return app(CreateAddressAction::class)(UpsertAddressDTO::fromArray([
                            ...$data,
                            'user_id' => $owner->getKey(),
                        ]));
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('addresses.actions.edit'))
                    ->using(function (Model $record, array $data): Model {
                        /** @var Address $record */
                        return app(UpdateAddressAction::class)($record, UpsertAddressDTO::fromArray([
                            ...$data,
                            'user_id' => $record->user_id,
                        ]));
                    }),
                DeleteAction::make()
                    ->label(__('addresses.actions.delete'))
                    ->requiresConfirmation()
                    ->using(function (Model $record): bool {
                        /** @var Address $record */
                        app(DeleteAddressAction::class)($record);

                        return true;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('addresses.actions.delete_selected'))
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedMapPin)
            ->emptyStateHeading(__('addresses.empty.heading'))
            ->emptyStateDescription(__('addresses.empty.description'))
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('addresses.actions.create'))
                    ->icon(Heroicon::Plus)
                    ->using(function (array $data, string $model): Model {
                        /** @var User $owner */
                        $owner = $this->getOwnerRecord();

                        return app(CreateAddressAction::class)(UpsertAddressDTO::fromArray([
                            ...$data,
                            'user_id' => $owner->getKey(),
                        ]));
                    }),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
