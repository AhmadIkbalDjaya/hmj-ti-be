<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StoreComplaintRequest;
use App\Http\Resources\Guest\ComplaintDetailResource;
use App\Models\Complaint;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class ComplaintController extends Controller
{
    use HttpResponses;

    public function store(StoreComplaintRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $complaint = Complaint::create($validated);

            return $this->respondCreated(new ComplaintDetailResource($complaint));
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }
}
