<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'photo' => $this->photo_url,
            'position_id' => $this->position_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], fn ($value) => ! is_null($value));
    }
}
