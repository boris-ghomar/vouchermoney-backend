<?php

namespace App\Http\Requests\Vouchers;

use App\Http\Requests\ApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="RedeemVoucherRequest",
 *     type="object",
 *     required={"code"},
 *     @OA\Property(property="code", type="string", example="XXXX-XXXX-XXXX-XXXX-XXXX-XXXX", description="The voucher code to redeem"),
 *     @OA\Property(property="note", type="string", maxLength=200, example="Redeemed for a special offer", description="Optional note for the voucher redemption")
 * )
 *
 * @property  string       $code
 * @property  string|null  $note
 */
class RedeemVoucherRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|min:14|max:32',
            'note' => 'nullable|string|max:200',
        ];
    }
}
