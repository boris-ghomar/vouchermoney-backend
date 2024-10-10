<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            "id" => $this->id,
            "code" => $this->code,
            "amount" => $this->amount,
            "state" => $this->active ? 'Active' : 'Frozen',
            "customer" => $this->customer->name,
            "creator_type" => $this->creator_type,
            "creator_id" => $this->creator_id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
