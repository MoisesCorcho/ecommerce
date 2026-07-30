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
                    ->label(__('products.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): ?string => $record->slug)
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label(__('products.fields.category'))
                    ->placeholder(__('products.placeholders.no_category'))
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label(__('products.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state
                        ? __('products.status.published')
                        : __('products.status.draft'))
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable(),
                IconColumn::make('is_preorder')
                    ->label(__('products.fields.is_preorder'))
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedClock)
                    ->falseIcon(Heroicon::OutlinedMinus)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label(__('products.fields.variants_count'))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('created_at')
                    ->label(__('products.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('products.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('products.fields.deleted_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('products.filters.published'))
                    ->placeholder(__('products.placeholders.filter_all'))
                    ->trueLabel(__('products.filters.published_only'))
                    ->falseLabel(__('products.filters.drafts_only')),
                TernaryFilter::make('is_preorder')
                    ->label(__('products.filters.preorder'))
                    ->placeholder(__('products.placeholders.filter_all'))
                    ->trueLabel(__('products.filters.preorder_only'))
                    ->falseLabel(__('products.filters.no_preorder')),
                SelectFilter::make('category_id')
                    ->label(__('products.fields.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make()
                    ->label(__('products.filters.trashed')),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('products.actions.edit')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('products.actions.move_to_trash'))
                        ->requiresConfirmation()
                        ->modalHeading(__('products.modals.delete_bulk_heading'))
                        ->modalDescription(__('products.modals.delete_bulk_description'))
                        ->modalSubmitActionLabel(__('products.actions.confirm_delete')),
                    RestoreBulkAction::make()
                        ->label(__('products.actions.restore_selected')),
                    ForceDeleteBulkAction::make()
                        ->label(__('products.actions.force_delete'))
                        ->requiresConfirmation()
                        ->modalHeading(__('products.modals.force_delete_heading'))
                        ->modalDescription(__('products.modals.force_delete_description'))
                        ->modalSubmitActionLabel(__('products.actions.confirm_force_delete')),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->emptyStateHeading(__('products.empty.heading'))
            ->emptyStateDescription(__('products.empty.description'))
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('products.actions.create'))
                    ->icon(Heroicon::Plus),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
