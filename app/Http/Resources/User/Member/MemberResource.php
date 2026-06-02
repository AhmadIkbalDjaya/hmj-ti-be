<?php

namespace App\Http\Resources\User\Member;

use App\Http\Resources\IdNameResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'photo' => $this->photo,
            'position' => new IdNameResource($this->position),
        ], fn ($value) => ! is_null($value));
    }
}
