<?php

namespace App\Http\Resources\User\Complaint;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'institute' => $this->institute,
            'description' => $this->description,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at,
        ];
    }
}
