<?php

namespace App\Http\Requests\Vouchers;

use App\Http\Requests\ApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @property string $code
 */
class FreezeVoucherRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|exists:vouchers,code'
        ];
    }
}
