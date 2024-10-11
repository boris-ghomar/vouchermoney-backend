<?php

namespace App\Http\Resources;

use App\Models\Voucher\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
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
