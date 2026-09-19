<?php

declare(strict_types=1);

namespace App\Http\Requests\Localization;

use App\Enums\Localization\LocaleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocaleRequest extends FormRequest
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
            'locale' => ['required', Rule::enum(LocaleEnum::class)],
        ];
    }
}
