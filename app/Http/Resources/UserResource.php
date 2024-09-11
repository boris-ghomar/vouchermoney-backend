<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "name" => $this->name,
            "username" => $this->username,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at,
            "parent_id" => $this->parent_id,
            "is_active" => $this->is_active,
            "timezone" => $this->timezone,
            "roles" => $this->roles->pluck("name"),
            "permissions" => $this->permissions->pluck("name"),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
