<?php

namespace App\Http\Requests\Vouchers;

use App\Http\Requests\ApiRequest;

/**
 * @OA\Schema(
 *     schema="ViewVoucherRequest",
 *     type="object",
 *     required={"code"},
 *     @OA\Property(property="code", type="string", example="XXXX-XXXX-XXXX-XXXX-XXXX-XXXX", description="The voucher code that need to check")
 * )
 *
 * @property string $code
 */
class ViewVoucherRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            "code" => "required|string|min:14|max:32"
        ];
    }
}
