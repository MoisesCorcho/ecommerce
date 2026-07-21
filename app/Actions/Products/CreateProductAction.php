<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\DTOs\Products\UpsertProductDTO;
use App\DTOs\Products\UpsertProductImageDTO;
use App\DTOs\Products\UpsertProductVariantDTO;
use App\Exceptions\Products\ProductCannotBePublishedException;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateProductAction
{
    /**
     * @throws ProductCannotBePublishedException
     * @throws ValidationException
     * @throws Throwable
     */
    public function __invoke(UpsertProductDTO $dto): Product
    {
        $this->assertCanPublish($dto);

        return DB::transaction(function () use ($dto): Product {
            $product = Product::query()->create([
                'category_id' => $dto->categoryId,
                'name' => $dto->name,
                'slug' => $this->resolveSlug($dto->slug, $dto->name),
                'description' => $dto->description,
                'material' => $dto->material,
                'dimensions' => $dto->dimensions,
                'is_preorder' => $dto->isPreorder,
                'is_active' => $dto->isActive,
            ]);

            foreach ($dto->variants as $variantDto) {
                $this->createVariant($product, $variantDto);
            }

            $this->syncImages($product, $dto->images);

            return $product->fresh(['variants.prices', 'images', 'category']) ?? $product;
        });
    }

    private function createVariant(Product $product, UpsertProductVariantDTO $variantDto): ProductVariant
    {
        $this->assertSkuUnique($variantDto->sku);

        $variant = $product->variants()->create([
            'sku' => $variantDto->sku,
            'color' => $variantDto->color,
            'size' => $variantDto->size,
            'stock' => $variantDto->stock,
            'is_active' => $variantDto->isActive,
        ]);

        foreach ($variantDto->prices as $priceDto) {
            ProductVariantPrice::query()->create([
                'product_variant_id' => $variant->id,
                'currency' => $priceDto->currency,
                'price' => $priceDto->price,
                'compare_at_price' => $priceDto->compareAtPrice,
            ]);
        }

        return $variant;
    }

    /**
     * @param  list<UpsertProductImageDTO>  $images
     */
    private function syncImages(Product $product, array $images): void
    {
        if ($images === []) {
            return;
        }

        $hasPrimary = false;
        foreach ($images as $index => $imageDto) {
            $isPrimary = $imageDto->isPrimary;
            if ($isPrimary) {
                if ($hasPrimary) {
                    $isPrimary = false;
                } else {
                    $hasPrimary = true;
                }
            }

            ProductImage::query()->create([
                'product_id' => $product->id,
                'product_variant_id' => $imageDto->productVariantId,
                'path' => $imageDto->path,
                'sort_order' => $index,
                'is_primary' => $isPrimary,
            ]);
        }

        if (! $hasPrimary) {
            $first = $product->images()->orderBy('sort_order')->first();
            if ($first !== null) {
                $first->update(['is_primary' => true]);
            }
        }
    }

    private function resolveSlug(?string $slug, string $name): string
    {
        $base = filled($slug) ? Str::slug($slug) : Str::slug($name);
        if ($base === '') {
            $base = 'producto';
        }

        $candidate = $base;
        $suffix = 1;
        while (Product::query()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function assertSkuUnique(string $sku, ?int $ignoreId = null): void
    {
        $query = ProductVariant::query()->where('sku', $sku);
        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'variants' => __('products.validation.sku_unique', ['sku' => $sku]),
            ]);
        }
    }

    /**
     * @throws ProductCannotBePublishedException
     */
    private function assertCanPublish(UpsertProductDTO $dto): void
    {
        if (! $dto->isActive) {
            return;
        }

        foreach ($dto->variants as $variant) {
            if ($variant->isActive && $variant->prices !== []) {
                return;
            }
        }

        throw ProductCannotBePublishedException::missingActiveVariantWithPrice();
    }
}
