<?php

namespace App\Http\Resources\Guest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationalStructureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'level' => (int) $this->level,
            'order_index' => (int) $this->order_index,
            'assigned_members' => $this->members->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'photo' => $member->photo_url,
                'position' => $this->name,
            ]),
            'children' => OrganizationalStructureResource::collection($this->children),
        ];
    }
}
