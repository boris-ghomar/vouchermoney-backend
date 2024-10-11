<?php

namespace App\Http\Resources;

use App\Models\Voucher\ArchivedVoucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
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
