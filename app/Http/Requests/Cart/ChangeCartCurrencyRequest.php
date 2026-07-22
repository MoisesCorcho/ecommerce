<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\Enums\Commerce\CurrencyEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ChangeCartCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<Enum|string>>
     */
    public function rules(): array
    {
        return [
            'currency' => ['required', 'string', Rule::enum(CurrencyEnum::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'currency' => __('cart.fields.currency'),
        ];
    }
}
