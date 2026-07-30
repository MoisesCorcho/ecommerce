<?php

declare(strict_types=1);

namespace App\Actions\Reviews;

use App\DTOs\Reviews\ProductReviewsSummaryDTO;
use App\Models\Product;
use App\Models\Review;

class GetProductReviewsSummaryAction
{
    public function __invoke(Product|int $product): ProductReviewsSummaryDTO
    {
        $productId = $product instanceof Product ? $product->id : $product;

        $aggregate = Review::query()
            ->where('product_id', $productId)
            ->approved()
            ->selectRaw('COUNT(*) as reviews_count, AVG(rating) as average_rating')
            ->first();

        $count = (int) ($aggregate?->reviews_count ?? 0);
        $avg = $count > 0 ? round((float) $aggregate->average_rating, 2) : null;

        return new ProductReviewsSummaryDTO(
            reviewsCount: $count,
            averageRating: $avg,
        );
    }
}
