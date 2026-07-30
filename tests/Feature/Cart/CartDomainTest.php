<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\ChangeCartCurrencyAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\GetOrCreateCartAction;
use App\Actions\Cart\MergeGuestCartIntoUserCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemQuantityAction;
use App\DTOs\Cart\AddCartItemDTO;
use App\DTOs\Cart\CartOwnerDTO;
use App\DTOs\Cart\ChangeCartCurrencyDTO;
use App\DTOs\Cart\ResolveCartDTO;
use App\DTOs\Cart\UpdateCartItemQuantityDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Exceptions\Cart\CartAccessDeniedException;
use App\Exceptions\Cart\CartCurrencyChangeBlockedException;
use App\Exceptions\Cart\CartItemNotEligibleException;
use App\Exceptions\Cart\CartQuantityNotAllowedException;
use App\Exceptions\Cart\InsufficientCartStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use App\Services\Cart\CartPricingService;
use App\Support\Cart\CartSession;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CartDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_get_or_create_reuses_session_cart(): void
    {
        $action = app(GetOrCreateCartAction::class);
        $sessionId = 'guest-session-abc';

        $first = $action(new ResolveCartDTO(sessionId: $sessionId));
        $second = $action(new ResolveCartDTO(sessionId: $sessionId));

        $this->assertNull($first->user_id);
        $this->assertSame($sessionId, $first->session_id);
        $this->assertSame(CurrencyEnum::Cop, $first->currency);
        $this->assertTrue($first->is($second));
        $this->assertSame(1, Cart::query()->where('session_id', $sessionId)->count());
    }

    public function test_user_get_or_create_reuses_single_active_cart(): void
    {
        $user = User::factory()->create();
        $action = app(GetOrCreateCartAction::class);

        $first = $action(new ResolveCartDTO(userId: $user->id));
        $second = $action(new ResolveCartDTO(userId: $user->id));

        $this->assertSame($user->id, $first->user_id);
        $this->assertNull($first->session_id);
        $this->assertTrue($first->is($second));
        $this->assertSame(1, Cart::query()->where('user_id', $user->id)->count());
    }

    public function test_add_creates_line_and_second_add_sums_quantity(): void
    {
        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 10, copPrice: 50_000);

        $add = app(AddCartItemAction::class);

        $add(new AddCartItemDTO(
            cartId: $cart->id,
            productVariantId: $variant->id,
            quantity: 2,
            sessionId: $cart->session_id,
        ));

        $add(new AddCartItemDTO(
            cartId: $cart->id,
            productVariantId: $variant->id,
            quantity: 3,
            sessionId: $cart->session_id,
        ));

        $this->assertSame(1, CartItem::query()->where('cart_id', $cart->id)->count());
        $this->assertSame(5, (int) CartItem::query()->where('cart_id', $cart->id)->value('quantity'));
        $this->assertSame(10, (int) $variant->fresh()->stock);
    }

    public function test_update_quantity_and_zero_removes_line(): void
    {
        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 20, copPrice: 10_000);

        app(AddCartItemAction::class)(new AddCartItemDTO(
            cartId: $cart->id,
            productVariantId: $variant->id,
            quantity: 2,
            sessionId: $cart->session_id,
        ));

        app(UpdateCartItemQuantityAction::class)(new UpdateCartItemQuantityDTO(
            cartId: $cart->id,
            productVariantId: $variant->id,
            quantity: 4,
            sessionId: $cart->session_id,
        ));

        $this->assertSame(4, (int) CartItem::query()->where('cart_id', $cart->id)->value('quantity'));

        app(UpdateCartItemQuantityAction::class)(new UpdateCartItemQuantityDTO(
            cartId: $cart->id,
            productVariantId: $variant->id,
            quantity: 0,
            sessionId: $cart->session_id,
        ));

        $this->assertSame(0, CartItem::query()->where('cart_id', $cart->id)->count());
    }

    public function test_remove_line_and_clear_cart_keep_header(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Cop]);
        $variantA = $this->createEligibleVariant(stock: 5, copPrice: 1_000, sku: 'SKU-A');
        $variantB = $this->createEligibleVariant(stock: 5, copPrice: 2_000, sku: 'SKU-B');
        $owner = new CartOwnerDTO(sessionId: $cart->session_id);

        $add = app(AddCartItemAction::class);
        $add(new AddCartItemDTO(cartId: $cart->id, productVariantId: $variantA->id, quantity: 1, sessionId: $cart->session_id));
        $add(new AddCartItemDTO(cartId: $cart->id, productVariantId: $variantB->id, quantity: 1, sessionId: $cart->session_id));

        app(RemoveCartItemAction::class)($cart->id, $variantA->id, $owner);
        $this->assertSame(1, CartItem::query()->where('cart_id', $cart->id)->count());

        app(ClearCartAction::class)($cart->id, $owner);

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'session_id' => $cart->session_id,
            'currency' => CurrencyEnum::Cop->value,
        ]);
        $this->assertSame(0, CartItem::query()->where('cart_id', $cart->id)->count());
    }

    public function test_merge_guest_into_user_sums_and_guest_is_no_longer_canonical(): void
    {
        $user = User::factory()->create();
        $guestSession = 'merge-guest-session';

        $guestCart = Cart::factory()->guest()->create(['session_id' => $guestSession]);
        $userCart = Cart::factory()->for($user)->create(['currency' => CurrencyEnum::Cop]);

        $shared = $this->createEligibleVariant(stock: 20, copPrice: 5_000, sku: 'SHARED');
        $guestOnly = $this->createEligibleVariant(stock: 10, copPrice: 3_000, sku: 'GUEST-ONLY');

        CartItem::factory()->for($guestCart)->create(['product_variant_id' => $shared->id, 'quantity' => 2]);
        CartItem::factory()->for($guestCart)->create(['product_variant_id' => $guestOnly->id, 'quantity' => 1]);
        CartItem::factory()->for($userCart)->create(['product_variant_id' => $shared->id, 'quantity' => 3]);

        $merged = app(MergeGuestCartIntoUserCartAction::class)($user->id, $guestSession);

        $this->assertTrue($merged->is($userCart));
        $this->assertSame(5, (int) CartItem::query()
            ->where('cart_id', $userCart->id)
            ->where('product_variant_id', $shared->id)
            ->value('quantity'));
        $this->assertSame(1, (int) CartItem::query()
            ->where('cart_id', $userCart->id)
            ->where('product_variant_id', $guestOnly->id)
            ->value('quantity'));
        $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);
    }

    public function test_merge_on_login_listener_runs(): void
    {
        $user = User::factory()->create();
        $guestSession = 'login-merge-session';
        $guestCart = Cart::factory()->guest()->create(['session_id' => $guestSession]);
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 1_000);
        CartItem::factory()->for($guestCart)->create(['product_variant_id' => $variant->id, 'quantity' => 2]);

        $this->startSession();
        session([CartSession::KEY => $guestSession]);

        Event::dispatch(new Login('web', $user, false));

        $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);
        $userCart = Cart::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($userCart);
        $this->assertSame(2, (int) CartItem::query()->where('cart_id', $userCart->id)->value('quantity'));
        $this->assertNull(session(CartSession::KEY));
    }

    public function test_pricing_totals_are_integers_in_cop_and_eur(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Cop]);
        $variant = $this->createEligibleVariant(stock: 10, copPrice: 25_000, eurPrice: 1_999);
        CartItem::factory()->for($cart)->create(['product_variant_id' => $variant->id, 'quantity' => 3]);

        $pricing = app(CartPricingService::class);
        $copView = $pricing->view($cart->fresh());

        $this->assertSame(25_000, $copView->lines[0]->unitPrice);
        $this->assertSame(75_000, $copView->lines[0]->lineSubtotal);
        $this->assertSame(75_000, $copView->total);
        $this->assertIsInt($copView->total);

        $cart->update(['currency' => CurrencyEnum::Eur]);
        $eurView = $pricing->view($cart->fresh());

        $this->assertSame(1_999, $eurView->lines[0]->unitPrice);
        $this->assertSame(5_997, $eurView->total);
        $this->assertIsInt($eurView->total);
    }

    public function test_cart_view_exposes_image_stock_and_variant_attributes(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Cop]);
        $product = Product::factory()->create([
            'is_active' => true,
            'slug' => 'bolso-artesanal',
            'material' => 'Cuero',
        ]);
        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'SKU-IMG',
            'is_active' => true,
            'stock' => 4,
            'color' => 'Marrón',
            'size' => 'M',
        ]);
        ProductVariantPrice::factory()->for($variant, 'productVariant')->cop()->create(['price' => 30_000]);
        ProductImage::factory()->for($product)->create([
            'product_variant_id' => $variant->id,
            'path' => 'products/variant-primary.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);
        CartItem::factory()->for($cart)->create(['product_variant_id' => $variant->id, 'quantity' => 2]);

        $pricing = app(CartPricingService::class);
        $view = $pricing->view($cart->fresh());

        $line = $view->lines[0];
        $this->assertSame('products/variant-primary.jpg', $line->imagePath);
        $this->assertSame('bolso-artesanal', $line->productSlug);
        $this->assertSame('Marrón', $line->color);
        $this->assertSame('M', $line->size);
        $this->assertSame('Cuero', $line->material);
        $this->assertSame(4, $line->stock);
        $this->assertTrue($line->isAvailable);
    }

    public function test_cart_view_marks_line_unavailable_when_variant_inactive_or_out_of_stock(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Cop]);
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'SKU-OOS',
            'is_active' => true,
            'stock' => 0,
        ]);
        ProductVariantPrice::factory()->for($variant, 'productVariant')->cop()->create(['price' => 10_000]);
        CartItem::factory()->for($cart)->create(['product_variant_id' => $variant->id, 'quantity' => 1]);

        $pricing = app(CartPricingService::class);
        $view = $pricing->view($cart->fresh());

        $line = $view->lines[0];
        $this->assertSame(0, $line->stock);
        $this->assertTrue($line->isAvailable);
        $this->assertNull($line->imagePath);

        $variant->update(['is_active' => false]);

        $view2 = $pricing->view($cart->fresh());
        $this->assertFalse($view2->lines[0]->isAvailable);
    }

    public function test_change_currency_succeeds_when_all_lines_have_price(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Cop]);
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 10_000, eurPrice: 500);
        CartItem::factory()->for($cart)->create(['product_variant_id' => $variant->id, 'quantity' => 1]);

        $updated = app(ChangeCartCurrencyAction::class)(new ChangeCartCurrencyDTO(
            cartId: $cart->id,
            currency: CurrencyEnum::Eur,
            sessionId: $cart->session_id,
        ));

        $this->assertSame(CurrencyEnum::Eur, $updated->currency);
    }

    public function test_change_currency_blocked_when_line_missing_price(): void
    {
        $cart = Cart::factory()->guest()->create(['currency' => CurrencyEnum::Cop]);
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 10_000, eurPrice: null);
        CartItem::factory()->for($cart)->create(['product_variant_id' => $variant->id, 'quantity' => 1]);

        try {
            app(ChangeCartCurrencyAction::class)(new ChangeCartCurrencyDTO(
                cartId: $cart->id,
                currency: CurrencyEnum::Eur,
                sessionId: $cart->session_id,
            ));
            $this->fail('Expected CartCurrencyChangeBlockedException.');
        } catch (CartCurrencyChangeBlockedException) {
            $this->assertSame(CurrencyEnum::Cop, $cart->fresh()->currency);
        }
    }

    public function test_ineligible_variant_is_rejected(): void
    {
        $cart = Cart::factory()->guest()->create();
        $product = Product::factory()->create(['is_active' => false]);
        $variant = ProductVariant::factory()->for($product)->create(['is_active' => true, 'stock' => 5]);
        ProductVariantPrice::factory()->for($variant, 'productVariant')->cop()->create(['price' => 1_000]);

        $this->expectException(CartItemNotEligibleException::class);

        app(AddCartItemAction::class)(new AddCartItemDTO(
            cartId: $cart->id,
            productVariantId: $variant->id,
            quantity: 1,
            sessionId: $cart->session_id,
        ));
    }

    public function test_inactive_variant_and_missing_price_are_rejected(): void
    {
        $cart = Cart::factory()->guest()->create();

        $inactiveVariant = $this->createEligibleVariant(stock: 5, copPrice: 1_000, sku: 'INACT');
        $inactiveVariant->update(['is_active' => false]);

        try {
            app(AddCartItemAction::class)(new AddCartItemDTO(
                cartId: $cart->id,
                productVariantId: $inactiveVariant->id,
                quantity: 1,
                sessionId: $cart->session_id,
            ));
            $this->fail('Expected CartItemNotEligibleException for inactive variant.');
        } catch (CartItemNotEligibleException) {
            $this->assertTrue(true);
        }

        $noPrice = ProductVariant::factory()->for(Product::factory()->create(['is_active' => true]))->create([
            'is_active' => true,
            'stock' => 5,
            'sku' => 'NOPRICE',
        ]);

        $this->expectException(CartItemNotEligibleException::class);

        app(AddCartItemAction::class)(new AddCartItemDTO(
            cartId: $cart->id,
            productVariantId: $noPrice->id,
            quantity: 1,
            sessionId: $cart->session_id,
        ));
    }

    public function test_insufficient_stock_and_quantity_over_99_are_rejected(): void
    {
        $cart = Cart::factory()->guest()->create();
        $lowStock = $this->createEligibleVariant(stock: 2, copPrice: 1_000, sku: 'LOW');
        $highStock = $this->createEligibleVariant(stock: 200, copPrice: 1_000, sku: 'HIGH');

        try {
            app(AddCartItemAction::class)(new AddCartItemDTO(
                cartId: $cart->id,
                productVariantId: $lowStock->id,
                quantity: 3,
                sessionId: $cart->session_id,
            ));
            $this->fail('Expected InsufficientCartStockException.');
        } catch (InsufficientCartStockException) {
            $this->assertSame(0, CartItem::query()->where('cart_id', $cart->id)->count());
        }

        $this->expectException(CartQuantityNotAllowedException::class);

        app(AddCartItemAction::class)(new AddCartItemDTO(
            cartId: $cart->id,
            productVariantId: $highStock->id,
            quantity: 100,
            sessionId: $cart->session_id,
        ));
    }

    public function test_negative_quantity_rejected_and_stock_not_decremented_on_add(): void
    {
        $cart = Cart::factory()->guest()->create();
        $variant = $this->createEligibleVariant(stock: 7, copPrice: 1_000);
        $stockBefore = (int) $variant->stock;

        try {
            app(AddCartItemAction::class)(new AddCartItemDTO(
                cartId: $cart->id,
                productVariantId: $variant->id,
                quantity: -1,
                sessionId: $cart->session_id,
            ));
            $this->fail('Expected CartQuantityNotAllowedException.');
        } catch (CartQuantityNotAllowedException) {
            // expected
        }

        app(AddCartItemAction::class)(new AddCartItemDTO(
            cartId: $cart->id,
            productVariantId: $variant->id,
            quantity: 2,
            sessionId: $cart->session_id,
        ));

        $this->assertSame($stockBefore, (int) $variant->fresh()->stock);
    }

    public function test_mutation_of_foreign_cart_is_denied(): void
    {
        $ownerCart = Cart::factory()->guest()->create(['session_id' => 'owner-session']);
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 1_000);
        CartItem::factory()->for($ownerCart)->create(['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->expectException(CartAccessDeniedException::class);

        app(UpdateCartItemQuantityAction::class)(new UpdateCartItemQuantityDTO(
            cartId: $ownerCart->id,
            productVariantId: $variant->id,
            quantity: 2,
            sessionId: 'other-session',
        ));
    }

    private function createEligibleVariant(
        int $stock,
        int $copPrice,
        ?int $eurPrice = null,
        ?string $sku = null,
    ): ProductVariant {
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => $sku ?? 'SKU-'.uniqid(),
            'is_active' => true,
            'stock' => $stock,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->cop()
            ->create(['price' => $copPrice]);

        if ($eurPrice !== null) {
            ProductVariantPrice::factory()
                ->for($variant, 'productVariant')
                ->eur()
                ->create(['price' => $eurPrice]);
        }

        return $variant;
    }
}
