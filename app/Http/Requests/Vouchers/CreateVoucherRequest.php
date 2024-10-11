<?php

namespace App\Http\Requests\Vouchers;

use App\Http\Requests\ApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @property float $amount
 * @property int $count
 */
class CreateVoucherRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|between:1,10000',
            'count' => 'nullable|integer|min:1|max:25'
        ];
    }
}
