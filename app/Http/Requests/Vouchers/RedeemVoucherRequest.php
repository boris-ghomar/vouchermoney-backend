<?php

namespace App\Http\Requests\Vouchers;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property  string       $code
 * @property  string|null  $note
 */
class RedeemVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|exists:vouchers,code',
            'note' => 'nullable|string|max:200',
        ];
    }
}
