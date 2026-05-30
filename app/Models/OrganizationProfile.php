<?php

namespace App\Models;

use App\Observers\OrganizationProfileObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([OrganizationProfileObserver::class])]
class OrganizationProfile extends Model
{
    public const ABOUT_CACHE_KEY = 'guest.about.profile';

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
