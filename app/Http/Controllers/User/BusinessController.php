<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Requests\User\BulkDestroyBusinessRequest;
use App\Http\Requests\User\StoreBusinessRequest;
use App\Http\Requests\User\UpdateBusinessRequest;
use App\Http\Resources\MetaPaginateResource;
use App\Http\Resources\User\BusinessDetailResource;
use App\Http\Resources\User\BusinessResource;
use App\Models\Business;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    use HttpResponses;

    public function index(PaginateSearchRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = $request->input('search', '');
        $is_active = $request->input('is_active', null);

        $businesses = Business::query()
            ->select(['id', 'title', 'slug', 'price', 'is_active'])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('title', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%")
            ))
            ->when(! is_null($is_active), fn ($query) => $query->where('is_active', $is_active))
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $data = BusinessResource::collection($businesses);
        $meta = new MetaPaginateResource($businesses);

        return $this->respondSuccessWithMeta($data, $meta);
    }

    public function store(StoreBusinessRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['image'] = $request->file('image')->store('businesses');
            $new_business = Business::create($validated);

            return $this->respondCreated(new BusinessDetailResource($new_business));
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function show(Business $business): JsonResponse
    {
        return $this->respondSuccess(new BusinessDetailResource($business));
    }

    public function update(UpdateBusinessRequest $request, Business $business): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('businesses');
                if ($business->image && Storage::exists($business->image)) {
                    Storage::delete($business->image);
                }
            } else {
                unset($validated['image']);
            }

            $business->update($validated);

            return $this->respondSuccess(new BusinessDetailResource($business));
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function destroy(Business $business): JsonResponse
    {
        try {
            if ($business->image && Storage::exists($business->image)) {
                Storage::delete($business->image);
            }
            $business->delete();

            return $this->respondSuccess();
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function bulkDestroy(BulkDestroyBusinessRequest $request): JsonResponse
    {
        try {
            $deleted_count = 0;
            $failed_count = 0;

            if ($request->boolean('select_all')) {
                $exclude_ids = $request->input('exclude_ids', []);
                $filters = $request->input('filters', []);
                $search = $filters['search'] ?? '';
                $is_active = $filters['is_active'] ?? null;

                $query = Business::query()
                    ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('title', 'LIKE', "%$search%")
                        ->orWhere('description', 'LIKE', "%$search%")
                    ))
                    ->when(! is_null($is_active), fn ($query) => $query->where('is_active', $is_active))
                    ->whereNotIn('id', $exclude_ids);

                $businesses = $query->get();
            } else {
                $ids = $request->input('ids', []);
                $businesses = Business::whereIn('id', $ids)->get();
            }

            foreach ($businesses as $business) {
                try {
                    if ($business->image && Storage::exists($business->image)) {
                        Storage::delete($business->image);
                    }
                    $business->delete();
                    $deleted_count++;
                } catch (\Throwable $th) {
                    $failed_count++;
                }
            }

            $message = "$deleted_count bisnis berhasil dihapus.";

            return $this->respondSuccess([
                'deleted_count' => $deleted_count,
                'failed_count' => $failed_count,
            ], $message);
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }
}
