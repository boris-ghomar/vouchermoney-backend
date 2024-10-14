<?php

namespace App\Http\Requests\Vouchers;

use App\Http\Requests\ApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="FreezeVoucherRequest",
 *     type="object",
 *     required={"code"},
 *     @OA\Property(property="code", type="string", example="XXXX-XXXX-XXXX-XXXX-XXXX-XXXX", description="The voucher code to freeze")
 * )
 *
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
