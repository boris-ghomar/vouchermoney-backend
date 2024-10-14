<?php

namespace App\Http\Requests\Vouchers;

use App\Http\Requests\ApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CreateVoucherRequest",
 *     type="object",
 *     required={"amount"},
 *     @OA\Property(property="amount", type="number", format="float", example=100.00, description="Amount of the voucher, minimum 1, maximum 10.000"),
 *     @OA\Property(property="count", type="integer", example=1, description="Number of vouchers to create, defaults to 1, minimum 1, maximum 25")
 * )
 *
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
