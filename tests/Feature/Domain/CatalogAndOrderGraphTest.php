<?php

declare(strict_types=1);

namespace Tests\Feature\Domain;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentProviderEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAndOrderGraphTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_and_order_graph_is_wired_correctly(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@example.com',
        ]);

        $address = Address::factory()->for($user)->default()->create([
            'full_name' => 'Buyer Name',
            'country' => 'CO',
        ]);

        $category = Category::factory()->create([
            'name' => 'Piezas',
            'slug' => 'piezas',
        ]);

        $product = Product::factory()->for($category)->create([
            'name' => 'Honey Bag Medium',
            'slug' => 'honey-bag-medium',
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'LHB-HONEY-BLK',
            'color' => 'Negro',
            'stock' => 5,
        ]);

        $priceCop = ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->cop()
            ->create([
                'price' => 799_000,
                'compare_at_price' => 899_000,
            ]);

        $priceEur = ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->eur()
            ->create([
                'price' => 8_900,
            ]);

        $image = ProductImage::factory()
            ->for($product)
            ->primary()
            ->create([
                'product_variant_id' => $variant->id,
                'path' => 'products/honey-black.jpg',
            ]);

        $order = Order::factory()->for($user)->paid()->create([
            'email' => $user->email,
            'currency' => CurrencyEnum::Cop,
            'subtotal' => 799_000,
            'shipping_cost' => 15_000,
            'discount' => 0,
            'tax_amount' => 0,
            'total' => 814_000,
            'shipping_address_id' => $address->id,
            'shipping_full_name' => $address->full_name,
            'shipping_phone' => $address->phone,
            'shipping_address_line_1' => $address->address_line_1,
            'shipping_city' => $address->city,
            'shipping_state' => $address->state,
            'shipping_country' => $address->country,
        ]);

        $item = OrderItem::factory()->for($order)->create([
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_label' => $variant->color,
            'sku' => $variant->sku,
            'unit_price' => $priceCop->price,
            'quantity' => 1,
        ]);

        $payment = Payment::factory()->for($order)->approved()->create([
            'provider' => PaymentProviderEnum::Bold,
            'currency' => CurrencyEnum::Cop,
            'amount' => $order->total,
            'payment_method' => 'pse',
        ]);

        $user = $user->fresh(['addresses', 'orders.items.productVariant', 'orders.payments', 'orders.shippingAddress']);
        $product = $product->fresh(['category', 'variants.prices', 'images']);
        $order = $order->fresh(['user', 'items', 'payments', 'shippingAddress']);

        $this->assertTrue($user->addresses->contains($address));
        $this->assertTrue($user->orders->contains($order));

        $this->assertTrue($product->category->is($category));
        $this->assertTrue($product->variants->contains($variant));
        $this->assertTrue($product->images->contains($image));

        $loadedVariant = $product->variants->firstWhere('id', $variant->id);
        $this->assertNotNull($loadedVariant);
        $this->assertCount(2, $loadedVariant->prices);
        $this->assertTrue($loadedVariant->prices->contains(
            fn (ProductVariantPrice $price): bool => $price->is($priceCop) && $price->currency === CurrencyEnum::Cop
        ));
        $this->assertTrue($loadedVariant->prices->contains(
            fn (ProductVariantPrice $price): bool => $price->is($priceEur) && $price->currency === CurrencyEnum::Eur
        ));
        $this->assertSame(799_000, $priceCop->fresh()->price);

        $this->assertTrue($order->user->is($user));
        $this->assertTrue($order->shippingAddress->is($address));
        $this->assertTrue($order->items->contains($item));
        $this->assertTrue($order->payments->contains($payment));

        $this->assertSame(OrderStatusEnum::Paid, $order->status);
        $this->assertSame(CurrencyEnum::Cop, $order->currency);
        $this->assertInstanceOf(CurrencyEnum::class, $order->currency);

        $this->assertTrue($item->fresh()->productVariant->is($variant));
        $this->assertSame('Honey Bag Medium', $item->product_name);
        $this->assertSame('LHB-HONEY-BLK', $item->sku);
        $this->assertSame(799_000, $item->unit_price);

        $this->assertSame(PaymentProviderEnum::Bold, $payment->fresh()->provider);
        $this->assertSame(PaymentStatusEnum::Approved, $payment->fresh()->status);
        $this->assertSame(814_000, $payment->amount);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_order_item_survives_when_product_variant_is_deleted(): void
    {
        $variant = ProductVariant::factory()->create();
        $order = Order::factory()->create();
        $item = OrderItem::factory()->for($order)->create([
            'product_variant_id' => $variant->id,
            'product_name' => 'Snapshot Name',
            'sku' => $variant->sku,
            'unit_price' => 100_000,
            'quantity' => 1,
        ]);

        $variant->delete();

        $item = $item->fresh();

        $this->assertNotNull($item);
        $this->assertNull($item->product_variant_id);
        $this->assertSame('Snapshot Name', $item->product_name);
        $this->assertNull($item->productVariant);
    }
}
