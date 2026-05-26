<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Resources\Guest\CadreResource;
use App\Http\Resources\MetaPaginateResource;
use App\Models\Cadre;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class CadreController extends Controller
{
    use HttpResponses;

    public function index(PaginateSearchRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = $request->input('search', '');
        $batch = $request->input('batch', null);
        $status = $request->input('status', null);

        $cadres = Cadre::query()
            ->when($search, fn ($query) => $query->where('name', 'LIKE', "%$search%"))
            ->when($batch, fn ($query) => $query->where('batch', $batch))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $data = CadreResource::collection($cadres);
        $meta = new MetaPaginateResource($cadres);

        return $this->respondSuccessWithMeta($data, $meta);
    }
}
