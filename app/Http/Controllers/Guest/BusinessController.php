<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Resources\Guest\BusinessResource;
use App\Http\Resources\MetaPaginateResource;
use App\Models\Business;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class BusinessController extends Controller
{
    use HttpResponses;

    public function index(PaginateSearchRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $businesses = Business::query()
            ->active()
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $data = BusinessResource::collection($businesses);
        $meta = new MetaPaginateResource($businesses);

        return $this->respondSuccessWithMeta($data, $meta);
    }
}
