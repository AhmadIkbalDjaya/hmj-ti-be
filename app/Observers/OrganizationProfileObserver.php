<?php

namespace App\Observers;

use App\Models\OrganizationProfile;
use Illuminate\Support\Facades\Cache;

class OrganizationProfileObserver
{
    public function updated(OrganizationProfile $organizationProfile): void
    {
        Cache::forget(OrganizationProfile::ABOUT_CACHE_KEY);
    }
}
