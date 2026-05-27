<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\OrganizationProfile\UpdateOrganizationProfileRequest;
use App\Http\Resources\User\OrganizationProfile\OrganizationProfileResource;
use App\Models\OrganizationProfile;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class OrganizationProfileController extends Controller
{
    use HttpResponses;

    public function show(): JsonResponse
    {
        $profile = OrganizationProfile::firstOrCreate(['id' => 1]);

        return $this->respondSuccess(new OrganizationProfileResource($profile));
    }

    public function update(UpdateOrganizationProfileRequest $request): JsonResponse
    {
        try {
            $profile = OrganizationProfile::firstOrNew(['id' => 1]);
            $validated = $request->validated();

            foreach (['main_image', 'secondary_image'] as $field) {
                if ($request->hasFile($field)) {
                    $validated[$field] = $request->file($field)->store('organization-profiles');

                    if ($profile->{$field} && Storage::exists($profile->{$field})) {
                        Storage::delete($profile->{$field});
                    }
                } else {
                    unset($validated[$field]);
                }
            }

            $profile->fill($validated);
            $profile->save();

            return $this->respondSuccess(new OrganizationProfileResource($profile));
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }
}
