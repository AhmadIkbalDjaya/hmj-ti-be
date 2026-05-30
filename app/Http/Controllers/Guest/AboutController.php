<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Guest\AboutResource;
use App\Models\OrganizationProfile;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AboutController extends Controller
{
    use HttpResponses;

    public function show(): JsonResponse
    {
        $profile = Cache::remember(
            OrganizationProfile::ABOUT_CACHE_KEY,
            now()->addDay(),
            fn () => OrganizationProfile::firstOrCreate(['id' => 1])
        );

        return $this->respondSuccess(new AboutResource($profile));
    }
}
