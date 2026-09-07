<?php

namespace App\Http\Requests;

use App\Support\KenyanPhone;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product' => ['required', 'string', 'exists:bundle_mappings,slug'],
            'phone' => [
                'required',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || KenyanPhone::normalize($value) === null) {
                        $fail('Enter a valid Kenyan mobile number.');
                    }
                },
            ],
        ];
    }
}
