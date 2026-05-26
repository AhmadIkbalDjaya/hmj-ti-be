<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class OrganizationProfile extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'missions' => 'array',
    ];

    protected function mainImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->main_image ? asset("storage/{$this->main_image}") : null,
        );
    }

    protected function secondaryImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->secondary_image ? asset("storage/{$this->secondary_image}") : null,
        );
    }
}
