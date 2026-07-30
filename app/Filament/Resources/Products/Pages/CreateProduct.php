<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Products\CreateProductAction;
use App\DTOs\Products\UpsertProductDTO;
use App\Exceptions\Products\ProductCannotBePublishedException;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('products.pages.create_title');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('products.notifications.created');
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
