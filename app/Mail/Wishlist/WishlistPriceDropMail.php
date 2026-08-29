<?php

declare(strict_types=1);

namespace App\Mail\Wishlist;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class WishlistPriceDropMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly ProductVariant $variant,
        public readonly int $oldPrice,
        public readonly int $newPrice,
        public readonly CurrencyEnum $currency,
    ) {}

    public function envelope(): Envelope
    {
        $productName = $this->variant->product->name;

        return new Envelope(
            subject: __('wishlist.mail.price_drop_subject', ['product' => $productName]),
        );
    }

    public function content(): Content
    {
        $product = $this->variant->product;
        $primaryImage = $this->variant->images->firstWhere('is_primary', true)
            ?? $this->variant->images->first()
            ?? $product->primaryImage();

        $imageUrl = $primaryImage !== null
            ? asset('storage/'.$primaryImage->path)
            : asset('images/logos/leen-brown.png');

        $productUrl = route('products.show', ['slug' => $product->slug]);

        return new Content(
            markdown: 'mail.wishlist.price-drop',
            with: [
                'user' => $this->user,
                'product' => $product,
                'variant' => $this->variant,
                'oldPriceFormatted' => $this->currency->format($this->oldPrice),
                'newPriceFormatted' => $this->currency->format($this->newPrice),
                'imageUrl' => $imageUrl,
                'productUrl' => $productUrl,
                'logoUrl' => asset('images/logos/leen-brown.png'),
            ],
        );
    }
}
