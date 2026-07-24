<?php

declare(strict_types=1);

namespace App\DTOs\Reviews;

readonly class UpsertReviewDTO
{
    public function __construct(
        public int $productId,
        public int $rating,
        public ?string $comment,
    ) {}

    /**
     * @param  array{product_id?: mixed, rating?: mixed, comment?: mixed}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) ($data['product_id'] ?? 0),
            rating: (int) ($data['rating'] ?? 0),
            comment: self::normalizeComment($data['comment'] ?? null),
        );
    }

    public static function normalizeComment(mixed $comment): ?string
    {
        if ($comment === null) {
            return null;
        }

        $plain = trim(strip_tags((string) $comment));

        return $plain === '' ? null : $plain;
    }
}
