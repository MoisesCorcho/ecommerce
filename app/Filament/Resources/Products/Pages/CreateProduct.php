<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Products\CreateProductAction;
use App\DTOs\Products\UpsertProductDTO;
use App\Exceptions\Products\ProductCannotBePublishedException;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Nuevo producto';

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Producto creado';
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(CreateProductAction::class)(UpsertProductDTO::fromArray($data));
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
