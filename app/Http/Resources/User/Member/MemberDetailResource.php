<?php

namespace App\Http\Resources\User\Member;

use App\Http\Resources\IdNameResource;
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
            'gender' => $this->gender,
            'photo' => $this->photo_url,
            'position' => new IdNameResource($this->position),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], fn ($value) => ! is_null($value));
    }
}
