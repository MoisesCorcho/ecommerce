<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

final class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): ?string => $record->slug)
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->placeholder('Sin categoría')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Publicado' : 'Borrador')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable(),
                IconColumn::make('is_preorder')
                    ->label('Preventa')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedClock)
                    ->falseIcon(Heroicon::OutlinedMinus)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variantes')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Eliminado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Publicado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo publicados')
                    ->falseLabel('Solo borradores'),
                TernaryFilter::make('is_preorder')
                    ->label('Preventa')
                    ->placeholder('Todos')
                    ->trueLabel('En preventa')
                    ->falseLabel('Sin preventa'),
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make()
                    ->label('Eliminados'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Mover a papelera')
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar productos')
                        ->modalDescription('Se moverán a la papelera (soft delete). Podrás restaurarlos después.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                    RestoreBulkAction::make()
                        ->label('Restaurar seleccionados'),
                    ForceDeleteBulkAction::make()
                        ->label('Eliminar definitivamente')
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar definitivamente')
                        ->modalDescription('Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Eliminar para siempre'),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->emptyStateHeading('No hay productos todavía')
            ->emptyStateDescription('Crea el primer producto con al menos una variante y precio para poder publicarlo.')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Nuevo producto')
                    ->icon(Heroicon::Plus),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
