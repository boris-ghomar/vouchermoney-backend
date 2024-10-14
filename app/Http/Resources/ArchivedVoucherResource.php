<?php

namespace App\Http\Resources;

use App\Models\Voucher\ArchivedVoucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ArchivedVoucherResource",
 *     type="object",
 *     @OA\Property(property="code", type="string", example="XXXX-XXXX-XXXX-XXXX-XXXX-XXXX"),
 *     @OA\Property(property="amount", type="number", format="float", example=100.00),
 *     @OA\Property(property="note", type="string", example="Redeemed for a special offer")
 * )
 *
 * @mixin ArchivedVoucher
 */
class ArchivedVoucherResource extends JsonResource
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
            "note" => $this->note,
        ];
    }
}
