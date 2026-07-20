<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Products\UpdateProductAction;
use App\DTOs\Products\UpsertProductDTO;
use App\Exceptions\Products\ProductCannotBePublishedException;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Product $product */
        $product = $this->getRecord();
        $product->load(['variants.prices', 'images']);

        $data['variants'] = $product->variants->map(static function ($variant): array {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'color' => $variant->color,
                'size' => $variant->size,
                'stock' => $variant->stock,
                'is_active' => $variant->is_active,
                'prices' => $variant->prices->map(static function ($price): array {
                    return [
                        'id' => $price->id,
                        'currency' => $price->currency->value,
                        'price' => $price->price,
                        'compare_at_price' => $price->compare_at_price,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $data['images'] = $product->images->map(static function ($image): array {
            return [
                'id' => $image->id,
                'path' => $image->path,
                'sort_order' => $image->sort_order,
                'is_primary' => $image->is_primary,
            ];
        })->values()->all();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Product $record */
        try {
            return app(UpdateProductAction::class)($record, UpsertProductDTO::fromArray($data));
        } catch (ProductCannotBePublishedException $exception) {
            throw ValidationException::withMessages([
                'data.is_active' => $exception->getMessage(),
            ]);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'data.variants' => $exception->getMessage(),
            ]);
        }
    }
}
