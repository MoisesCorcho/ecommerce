<?php

use App\Actions\Orders\CreateOrderFromCartAction;
use App\Actions\Orders\ValidateCartForCheckoutAction;
use App\DTOs\Orders\CheckoutContactDTO;
use App\DTOs\Orders\CheckoutShippingDTO;
use App\DTOs\Orders\CreateOrderFromCartDTO;
use App\Exceptions\Coupons\InvalidCouponException;
use App\Exceptions\Orders\CheckoutCartEmptyException;
use App\Exceptions\Orders\CheckoutCartNotReadyException;
use App\Exceptions\Orders\InvalidCheckoutAddressException;
use App\Exceptions\Orders\OrderAccessDeniedException;
use App\Models\Address;
use App\Support\Cart\ResolvesCurrentCart;
use App\Support\Coupons\CouponAttemptRateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
{
    use ResolvesCurrentCart;

    public function render()
    {
        return $this->view()->title('Leen Handbags | '.__('orders.checkout.title'));
    }

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public string $shippingFullName = '';

    public string $shippingPhone = '';

    public string $shippingAddressLine1 = '';

    public string $shippingAddressLine2 = '';

    public string $shippingCity = '';

    public string $shippingState = '';

    public string $shippingCountry = 'CO';

    public string $shippingPostalCode = '';

    public ?int $shippingAddressId = null;

    public string $addressMode = 'one_shot';

    public string $customerNotes = '';

    public string $couponCode = '';

    public ?string $errorMessage = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $preview = null;

    public function mount(ValidateCartForCheckoutAction $validateCartForCheckout): void
    {
        $user = Auth::user();

        if ($user !== null) {
            $parts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
            $this->firstName = $parts[0] ?? '';
            $this->lastName = $parts[1] ?? '';
            $this->email = (string) $user->email;
            $this->phone = (string) ($user->phone ?? '');
            $this->addressMode = 'saved';
        }

        $this->loadPreview($validateCartForCheckout);
    }

    public function updatedCouponCode(ValidateCartForCheckoutAction $validateCartForCheckout): void
    {
        $this->errorMessage = null;

        if (! $this->consumeCouponRateLimitIfNeeded()) {
            return;
        }

        $this->loadPreview($validateCartForCheckout);
    }

    public function applyCoupon(ValidateCartForCheckoutAction $validateCartForCheckout): void
    {
        $this->errorMessage = null;

        if (! $this->consumeCouponRateLimitIfNeeded()) {
            return;
        }

        $this->loadPreview($validateCartForCheckout);
    }

    public function updatedShippingAddressId(): void
    {
        if ($this->shippingAddressId === null || Auth::id() === null) {
            return;
        }

        $address = Address::query()
            ->whereKey($this->shippingAddressId)
            ->where('user_id', Auth::id())
            ->first();

        if ($address === null) {
            return;
        }

        $this->shippingFullName = $address->full_name;
        $this->shippingPhone = $address->phone;
        $this->shippingAddressLine1 = $address->address_line_1;
        $this->shippingAddressLine2 = (string) ($address->address_line_2 ?? '');
        $this->shippingCity = $address->city;
        $this->shippingState = $address->state;
        $this->shippingCountry = $address->country;
        $this->shippingPostalCode = (string) ($address->postal_code ?? '');
    }

    public function confirm(
        ValidateCartForCheckoutAction $validateCartForCheckout,
        CreateOrderFromCartAction $createOrderFromCart,
    ): mixed {
        $this->errorMessage = null;

        // Guest checkout (Auth::check() === false) is intentionally not gated here.
        if (Auth::check() && ! Auth::user()->hasVerifiedEmail()) {
            $this->errorMessage = __('auth.verify_email_required');

            return null;
        }

        $this->validate($this->rules());

        if (! $this->consumeCouponRateLimitIfNeeded()) {
            return null;
        }

        try {
            $this->loadPreview($validateCartForCheckout);

            $owner = $this->cartOwner();
            $cart = $this->resolveCurrentCart();

            $shipping = $this->buildShippingDto();

            $order = $createOrderFromCart(new CreateOrderFromCartDTO(
                cartId: (int) $cart->id,
                contact: new CheckoutContactDTO(
                    firstName: $this->firstName,
                    lastName: $this->lastName,
                    email: $this->email,
                    phone: $this->phone,
                ),
                shipping: $shipping,
                userId: $owner->userId,
                sessionId: $owner->sessionId,
                customerNotes: $this->customerNotes !== '' ? $this->customerNotes : null,
                couponCode: $this->normalizedCouponCode(),
            ));

            if ($owner->userId !== null) {
                return $this->redirect(route('orders.thank-you', $order), navigate: false);
            }

            $url = URL::temporarySignedRoute(
                'orders.thank-you',
                now()->addDays(7),
                ['order' => $order->id],
            );

            return $this->redirect($url, navigate: false);
        } catch (InvalidCouponException $e) {
            $this->errorMessage = $e->storefrontSafeMessage();

            return null;
        } catch (
            CheckoutCartEmptyException|
            CheckoutCartNotReadyException|
            OrderAccessDeniedException|
            InvalidCheckoutAddressException $e
        ) {
            $this->errorMessage = $e->getMessage();

            if ($e instanceof CheckoutCartEmptyException || $e instanceof CheckoutCartNotReadyException) {
                session()->flash('checkout_error', $e->getMessage());

                return $this->redirect(route('cart.page'), navigate: false);
            }

            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $addressRequired = $this->addressMode === 'one_shot' || $this->shippingAddressId === null;

        return [
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'shippingAddressId' => [
                $this->addressMode === 'saved' ? 'required' : 'nullable',
                'integer',
                'exists:addresses,id',
            ],
            'shippingFullName' => [$addressRequired ? 'required' : 'nullable', 'string', 'max:255'],
            'shippingPhone' => [$addressRequired ? 'required' : 'nullable', 'string', 'max:32'],
            'shippingAddressLine1' => [$addressRequired ? 'required' : 'nullable', 'string', 'max:255'],
            'shippingAddressLine2' => ['nullable', 'string', 'max:255'],
            'shippingCity' => [$addressRequired ? 'required' : 'nullable', 'string', 'max:120'],
            'shippingState' => [$addressRequired ? 'required' : 'nullable', 'string', 'max:120'],
            'shippingCountry' => [$addressRequired ? 'required' : 'nullable', 'string', 'size:2'],
            'shippingPostalCode' => ['nullable', 'string', 'max:32'],
            'customerNotes' => ['nullable', 'string', 'max:1000'],
            'addressMode' => ['required', 'in:saved,one_shot'],
            'couponCode' => ['nullable', 'string', 'max:32'],
        ];
    }

    private function buildShippingDto(): CheckoutShippingDTO
    {
        if ($this->addressMode === 'saved' && $this->shippingAddressId !== null && Auth::id() !== null) {
            return new CheckoutShippingDTO(
                fullName: $this->shippingFullName !== '' ? $this->shippingFullName : '—',
                phone: $this->shippingPhone !== '' ? $this->shippingPhone : '—',
                addressLine1: $this->shippingAddressLine1 !== '' ? $this->shippingAddressLine1 : '—',
                addressLine2: $this->shippingAddressLine2 !== '' ? $this->shippingAddressLine2 : null,
                city: $this->shippingCity !== '' ? $this->shippingCity : '—',
                state: $this->shippingState !== '' ? $this->shippingState : '—',
                country: $this->shippingCountry !== '' ? $this->shippingCountry : 'CO',
                postalCode: $this->shippingPostalCode !== '' ? $this->shippingPostalCode : null,
                addressId: $this->shippingAddressId,
            );
        }

        return new CheckoutShippingDTO(
            fullName: $this->shippingFullName !== ''
                ? $this->shippingFullName
                : trim($this->firstName.' '.$this->lastName),
            phone: $this->shippingPhone !== '' ? $this->shippingPhone : $this->phone,
            addressLine1: $this->shippingAddressLine1,
            addressLine2: $this->shippingAddressLine2 !== '' ? $this->shippingAddressLine2 : null,
            city: $this->shippingCity,
            state: $this->shippingState,
            country: strtoupper($this->shippingCountry),
            postalCode: $this->shippingPostalCode !== '' ? $this->shippingPostalCode : null,
            addressId: null,
        );
    }

    private function loadPreview(ValidateCartForCheckoutAction $validateCartForCheckout): void
    {
        try {
            $cart = $this->resolveCurrentCart();
            $preview = $validateCartForCheckout(
                (int) $cart->id,
                $this->cartOwner(),
                $this->normalizedCouponCode(),
            );

            $this->preview = [
                'cartId' => $preview->cartId,
                'currency' => $preview->currency->value,
                'subtotal' => $preview->subtotal,
                'shippingCost' => $preview->shippingCost,
                'discount' => $preview->discount,
                'taxAmount' => $preview->taxAmount,
                'total' => $preview->total,
                'lines' => array_map(
                    static fn ($line): array => [
                        'productVariantId' => $line->productVariantId,
                        'productName' => $line->productName,
                        'variantLabel' => $line->variantLabel,
                        'sku' => $line->sku,
                        'unitPrice' => $line->unitPrice,
                        'quantity' => $line->quantity,
                        'lineSubtotal' => $line->lineSubtotal,
                    ],
                    $preview->lines,
                ),
            ];
        } catch (InvalidCouponException $e) {
            $this->errorMessage = $e->storefrontSafeMessage();
            $this->loadPreviewWithoutCoupon($validateCartForCheckout);
        } catch (CheckoutCartEmptyException|CheckoutCartNotReadyException|OrderAccessDeniedException $e) {
            session()->flash('checkout_error', $e->getMessage());
            $this->redirect(route('cart.page'), navigate: false);
        }
    }

    private function loadPreviewWithoutCoupon(ValidateCartForCheckoutAction $validateCartForCheckout): void
    {
        try {
            $cart = $this->resolveCurrentCart();
            $preview = $validateCartForCheckout((int) $cart->id, $this->cartOwner(), null);

            $this->preview = [
                'cartId' => $preview->cartId,
                'currency' => $preview->currency->value,
                'subtotal' => $preview->subtotal,
                'shippingCost' => $preview->shippingCost,
                'discount' => $preview->discount,
                'taxAmount' => $preview->taxAmount,
                'total' => $preview->total,
                'lines' => array_map(
                    static fn ($line): array => [
                        'productVariantId' => $line->productVariantId,
                        'productName' => $line->productName,
                        'variantLabel' => $line->variantLabel,
                        'sku' => $line->sku,
                        'unitPrice' => $line->unitPrice,
                        'quantity' => $line->quantity,
                        'lineSubtotal' => $line->lineSubtotal,
                    ],
                    $preview->lines,
                ),
            ];
        } catch (CheckoutCartEmptyException|CheckoutCartNotReadyException|OrderAccessDeniedException $e) {
            session()->flash('checkout_error', $e->getMessage());
            $this->redirect(route('cart.page'), navigate: false);
        }
    }

    private function normalizedCouponCode(): ?string
    {
        $trimmed = trim($this->couponCode);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Rate-limit non-blank coupon attempts (preview apply / confirm) per user or IP.
     * Empty code does not count — avoids blocking normal checkout without coupons.
     */
    private function consumeCouponRateLimitIfNeeded(): bool
    {
        if ($this->normalizedCouponCode() === null) {
            return true;
        }

        $allowed = app(CouponAttemptRateLimiter::class)->attempt(
            userId: Auth::id() !== null ? (int) Auth::id() : null,
            ip: (string) request()->ip(),
        );

        if (! $allowed) {
            $this->errorMessage = __('coupons.errors.rate_limited');

            return false;
        }

        return true;
    }
};
