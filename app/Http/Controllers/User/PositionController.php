<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Requests\User\Position\StorePositionRequest;
use App\Http\Requests\User\Position\UpdatePositionRequest;
use App\Http\Resources\MetaPaginateResource;
use App\Http\Resources\User\Position\PositionDetailResource;
use App\Http\Resources\User\Position\PositionResource;
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
        $parent_id = $request->input('parent_id', null);
        $descendant_ids = ! is_null($parent_id)
            ? $this->getDescendantPositionIds((int) $parent_id)
            : null;

        $positions = Position::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'level', 'order_index', 'is_active'])
            ->when($search, fn ($query) => $query->where('name', 'LIKE', "%$search%"))
            ->when(! is_null($is_active), fn ($query) => $query->where('is_active', $is_active))
            ->when(! is_null($level), fn ($query) => $query->where('level', $level))
            ->when(! is_null($descendant_ids), fn ($query) => $query->whereIn('id', $descendant_ids))
            ->orderBy('level', 'asc')
            ->orderBy('order_index', 'asc')
            ->paginate($limit, ['*'], 'page', $page);

        $data = PositionResource::collection($positions);
        $meta = new MetaPaginateResource($positions);

        return $this->respondSuccessWithMeta($data, $meta);
    }

    private function getDescendantPositionIds(int $parent_id): array
    {
        $descendant_ids = [];
        $current_parent_ids = [$parent_id];

        while (! empty($current_parent_ids)) {
            $children_ids = Position::query()
                ->whereIn('parent_id', $current_parent_ids)
                ->pluck('id')
                ->all();

            $descendant_ids = [...$descendant_ids, ...$children_ids];
            $current_parent_ids = $children_ids;
        }

        return $descendant_ids;
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
