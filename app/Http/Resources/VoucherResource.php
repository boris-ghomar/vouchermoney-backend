<?php

namespace App\Http\Resources;

use App\Models\Voucher\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="VoucherResource",
 *     type="object",
 *     @OA\Property(property="code", type="string", example="XXXX-XXXX-XXXX-XXXX-XXXX-XXXX"),
 *     @OA\Property(property="amount", type="number", format="float", example=100.00),
 *     @OA\Property(property="state", type="string", example="active", description="The state of the voucher, either 'active' or 'frozen'")
 * )
 *
 * @mixin Voucher
 */
class VoucherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "code" => $this->code,
            "amount" => $this->amount,
            "state" => $this->active ? 'active' : 'frozen',
        ];
    }
}
