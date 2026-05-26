<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Guest\AboutResource;
use App\Models\OrganizationProfile;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class AboutController extends Controller
{
    use HttpResponses;

    public function show(): JsonResponse
    {
        $profile = OrganizationProfile::firstOrCreate(['id' => 1]);

        return $this->respondSuccess(new AboutResource($profile));
    }
}
