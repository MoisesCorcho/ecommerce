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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateProductAction
{
    /**
     * @throws ProductCannotBePublishedException
     * @throws ValidationException
     * @throws Throwable
     */
    public function __invoke(Product $product, UpsertProductDTO $dto): Product
    {
        $this->assertCanPublish($dto);

        return DB::transaction(function () use ($product, $dto): Product {
            $slug = $this->resolveSlug($dto->slug, $dto->name, $product);

            $product->update([
                'category_id' => $dto->categoryId,
                'name' => $dto->name,
                'slug' => $slug,
                'description' => $dto->description,
                'material' => $dto->material,
                'dimensions' => $dto->dimensions,
                'is_preorder' => $dto->isPreorder,
                'is_active' => $dto->isActive,
            ]);

            $this->syncVariants($product, $dto->variants);
            $this->syncImages($product, $dto->images);

            return $product->fresh(['variants.prices', 'images', 'category']) ?? $product;
        });
    }

    /**
     * @param  list<UpsertProductVariantDTO>  $variants
     */
    private function syncVariants(Product $product, array $variants): void
    {
        $keptIds = [];

        foreach ($variants as $variantDto) {
            $variant = $this->upsertVariant($product, $variantDto);
            $keptIds[] = $variant->id;
            $this->syncPrices($variant, $variantDto);
        }

        $toDelete = $product->variants()
            ->when($keptIds !== [], fn ($q) => $q->whereNotIn('id', $keptIds))
            ->get();

        foreach ($toDelete as $variant) {
            $variant->prices()->delete();
            $variant->delete();
        }
    }

    private function upsertVariant(Product $product, UpsertProductVariantDTO $variantDto): ProductVariant
    {
        if ($variantDto->id !== null) {
            $variant = $product->variants()->whereKey($variantDto->id)->first();
            if ($variant === null) {
                throw ValidationException::withMessages([
                    'variants' => __('products.validation.variant_not_owned'),
                ]);
            }

            $this->assertSkuUnique($variantDto->sku, $variant->id);

            $variant->update([
                'sku' => $variantDto->sku,
                'color' => $variantDto->color,
                'size' => $variantDto->size,
                'stock' => $variantDto->stock,
                'is_active' => $variantDto->isActive,
            ]);

            return $variant;
        }

        $this->assertSkuUnique($variantDto->sku);

        return $product->variants()->create([
            'sku' => $variantDto->sku,
            'color' => $variantDto->color,
            'size' => $variantDto->size,
            'stock' => $variantDto->stock,
            'is_active' => $variantDto->isActive,
        ]);
    }

    private function syncPrices(ProductVariant $variant, UpsertProductVariantDTO $variantDto): void
    {
        $keptIds = [];

        foreach ($variantDto->prices as $priceDto) {
            if ($priceDto->id !== null) {
                $price = $variant->prices()->whereKey($priceDto->id)->first();
                if ($price === null) {
                    throw ValidationException::withMessages([
                        'variants' => __('products.validation.price_not_owned'),
                    ]);
                }

                $price->update([
                    'currency' => $priceDto->currency,
                    'price' => $priceDto->price,
                    'compare_at_price' => $priceDto->compareAtPrice,
                ]);
                $keptIds[] = $price->id;

                continue;
            }

            $price = $variant->prices()->updateOrCreate(
                ['currency' => $priceDto->currency],
                [
                    'price' => $priceDto->price,
                    'compare_at_price' => $priceDto->compareAtPrice,
                ],
            );
            $keptIds[] = $price->id;
        }

        $variant->prices()
            ->whereNotIn('id', $keptIds === [] ? [0] : $keptIds)
            ->delete();
    }

    /**
     * @param  list<UpsertProductImageDTO>  $images
     */
    private function syncImages(Product $product, array $images): void
    {
        $keptIds = [];
        $primaryAssigned = false;

        foreach ($images as $index => $imageDto) {
            $isPrimary = $imageDto->isPrimary && ! $primaryAssigned;
            if ($isPrimary) {
                $primaryAssigned = true;
            }

            if ($imageDto->id !== null) {
                $image = $product->images()->whereKey($imageDto->id)->first();
                if ($image === null) {
                    throw ValidationException::withMessages([
                        'images' => __('products.validation.image_not_owned'),
                    ]);
                }

                $image->update([
                    'path' => $imageDto->path,
                    'sort_order' => $index,
                    'is_primary' => $isPrimary,
                    'product_variant_id' => $imageDto->productVariantId,
                ]);
                $keptIds[] = $image->id;

                continue;
            }

            $image = ProductImage::query()->create([
                'product_id' => $product->id,
                'product_variant_id' => $imageDto->productVariantId,
                'path' => $imageDto->path,
                'sort_order' => $index,
                'is_primary' => $isPrimary,
            ]);
            $keptIds[] = $image->id;
        }

        $product->images()
            ->whereNotIn('id', $keptIds === [] ? [0] : $keptIds)
            ->delete();

        if ($keptIds !== [] && ! $primaryAssigned) {
            $product->images()->whereIn('id', $keptIds)->update(['is_primary' => false]);
            $first = $product->images()->whereIn('id', $keptIds)->orderBy('sort_order')->first();
            $first?->update(['is_primary' => true]);
        }

        if ($keptIds === []) {
            return;
        }

        // Ensure at most one primary (DB-level safety after bulk flags).
        $primaries = $product->images()->where('is_primary', true)->orderBy('sort_order')->get();
        if ($primaries->count() > 1) {
            $primaries->skip(1)->each(fn (ProductImage $img) => $img->update(['is_primary' => false]));
        }
    }

    private function resolveSlug(?string $slug, string $name, Product $product): string
    {
        $base = filled($slug) ? Str::slug($slug) : Str::slug($name);
        if ($base === '') {
            $base = 'producto';
        }

        $exists = Product::query()
            ->where('slug', $base)
            ->whereKeyNot($product->getKey())
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'slug' => __('products.validation.slug_unique'),
            ]);
        }

        return $base;
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
