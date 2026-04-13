<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Resources\MetaPaginateResource;
use App\Http\Resources\User\ComplaintDetailResource;
use App\Http\Resources\User\ComplaintResource;
use App\Models\Complaint;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class ComplaintController extends Controller
{
    use HttpResponses;

    public function index(PaginateSearchRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = $request->input('search', '');

        $complaints = Complaint::query()
            ->select(['id', 'name', 'email', 'phone', 'institute', 'description'])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('name', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%")
            )
            )
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $data = ComplaintResource::collection($complaints);
        $meta = new MetaPaginateResource($complaints);

        return $this->respondSuccessWithMeta($data, $meta);
    }

    public function show(Complaint $complaint): JsonResponse
    {
        return $this->respondSuccess(new ComplaintDetailResource($complaint));
    }

    public function destroy(Complaint $complaint): JsonResponse
    {
        try {
            $complaint->delete();

            return $this->respondSuccess();
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }
}
