<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            "id" => $this->id,
            "code" => $this->code,
            "amount" => $this->amount,
            "state" => $this->state === 'expired' ? 'Expired' : 'Redeemed',
            "customer" =>  $this->customer_data['name'],
            "creator_id" => $this->creator_data['id'],
            "creator" => $this->creator_data['name'],
            "recipient" => $this->recipient_data['name'] ?? null,
            "recipient_id" => $this->recipient_data['id'] ?? null,
            "recipient_note" => $this->recipient_note,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
