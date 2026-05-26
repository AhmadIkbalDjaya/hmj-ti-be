<?php

namespace App\Http\Resources\Guest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'goal' => $this->goal,
            'vision' => $this->vision,
            'missions' => $this->missions ?? [],
            'main_image' => $this->main_image_url,
            'secondary_image' => $this->secondary_image_url,
        ];
    }
}
