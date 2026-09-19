<?php

declare(strict_types=1);

namespace App\Http\Requests\Commerce;

use App\Enums\Commerce\CurrencyEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'currency' => [
                'required',
                'string',
                Rule::enum(CurrencyEnum::class)->only(CurrencyEnum::storefrontCases()),
            ],
        ];
    }
}
