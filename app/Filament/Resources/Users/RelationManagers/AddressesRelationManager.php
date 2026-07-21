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

    protected static ?string $title = 'Direcciones';

    protected static ?string $modelLabel = 'dirección';

    protected static ?string $pluralModelLabel = 'direcciones';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedMapPin;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dirección de envío')
                    ->description('Datos que se usarán en el checkout. Solo una dirección puede ser predeterminada por usuario.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label('Etiqueta')
                                    ->placeholder('Casa, Oficina…')
                                    ->maxLength(64)
                                    ->columnSpan(1),
                                Toggle::make('is_default')
                                    ->label('Dirección predeterminada')
                                    ->helperText('Si la marcás, cualquier otra predeterminada del usuario se desmarca.')
                                    ->default(false)
                                    ->inline(false)
                                    ->columnSpan(1),
                                TextInput::make('full_name')
                                    ->label('Nombre completo')
                                    ->placeholder('Ana Pérez')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->placeholder('+57 300 123 4567')
                                    ->required()
                                    ->maxLength(32)
                                    ->columnSpan(1),
                                TextInput::make('address_line_1')
                                    ->label('Línea 1')
                                    ->placeholder('Calle 10 #20-30')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('address_line_2')
                                    ->label('Línea 2')
                                    ->placeholder('Apto 401, Torre B')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('city')
                                    ->label('Ciudad')
                                    ->placeholder('Medellín')
                                    ->required()
                                    ->maxLength(120)
                                    ->columnSpan(1),
                                TextInput::make('state')
                                    ->label('Estado / departamento')
                                    ->placeholder('Antioquia')
                                    ->required()
                                    ->maxLength(120)
                                    ->columnSpan(1),
                                TextInput::make('country')
                                    ->label('País (ISO)')
                                    ->placeholder('CO')
                                    ->required()
                                    ->length(2)
                                    ->default('CO')
                                    ->helperText('Código de 2 letras (ISO 3166-1 alpha-2).')
                                    ->maxLength(2)
                                    ->columnSpan(1),
                                TextInput::make('postal_code')
                                    ->label('Código postal')
                                    ->placeholder('050001')
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
            ->columns([
                TextColumn::make('label')
                    ->label('Etiqueta')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('full_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('state')
                    ->label('Estado')
                    ->toggleable(),
                TextColumn::make('country')
                    ->label('País')
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_default')
                    ->label('Predet.')
                    ->boolean(),
                TextColumn::make('address_line_1')
                    ->label('Dirección')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nueva dirección')
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
                    ->label('Editar')
                    ->using(function (Model $record, array $data): Model {
                        /** @var Address $record */
                        return app(UpdateAddressAction::class)($record, UpsertAddressDTO::fromArray([
                            ...$data,
                            'user_id' => $record->user_id,
                        ]));
                    }),
                DeleteAction::make()
                    ->label('Eliminar')
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
                        ->label('Eliminar seleccionadas')
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedMapPin)
            ->emptyStateHeading('Sin direcciones')
            ->emptyStateDescription('Agregá una dirección de envío para este usuario.')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Nueva dirección')
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
