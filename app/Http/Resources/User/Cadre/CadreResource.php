<?php

namespace App\Http\Resources\User\Cadre;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CadreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'batch' => $this->batch,
            'status' => $this->status,
        ];
    }
}
