<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Requests\User\StorePositionRequest;
use App\Http\Requests\User\UpdatePositionRequest;
use App\Http\Resources\MetaPaginateResource;
use App\Http\Resources\User\PositionDetailResource;
use App\Http\Resources\User\PositionResource;
use App\Models\Position;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class PositionController extends Controller
{
    use HttpResponses;

    public function index(PaginateSearchRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = $request->input('search', '');
        $is_active = $request->input('is_active', null);
        $level = $request->input('level', null);

        $positions = Position::query()
            ->select(['id', 'name', 'slug', 'level', 'is_active'])
            ->when($search, fn ($query) => $query->where('name', 'LIKE', "%$search%"))
            ->when(! is_null($is_active), fn ($query) => $query->where('is_active', $is_active))
            ->when(! is_null($level), fn ($query) => $query->where('level', $level))
            ->orderBy('level', 'asc')
            ->orderBy('order_index', 'asc')
            ->paginate($limit, ['*'], 'page', $page);

        $data = PositionResource::collection($positions);
        $meta = new MetaPaginateResource($positions);

        return $this->respondSuccessWithMeta($data, $meta);
    }

    public function store(StorePositionRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $new_position = Position::create($validated);

            return $this->respondCreated(new PositionDetailResource($new_position));
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function show(Position $position): JsonResponse
    {
        return $this->respondSuccess(new PositionDetailResource($position));
    }

    public function update(UpdatePositionRequest $request, Position $position): JsonResponse
    {
        try {
            $validated = $request->validated();
            $position->update($validated);

            return $this->respondSuccess(new PositionDetailResource($position));
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function destroy(Position $position): JsonResponse
    {
        try {
            $position->delete();

            return $this->respondSuccess();
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }
}
