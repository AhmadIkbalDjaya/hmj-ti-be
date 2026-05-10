<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Requests\User\BulkDestroyCadreRequest;
use App\Http\Requests\User\StoreCadreRequest;
use App\Http\Requests\User\UpdateCadreRequest;
use App\Http\Resources\MetaPaginateResource;
use App\Http\Resources\User\CadreResource;
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
            ->when($search, fn ($query) => $query->where('name', 'LIKE', "%$search%")
            )
            ->when($batch, fn ($query) => $query->where('batch', $batch)
            )
            ->when($status, fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $data = CadreResource::collection($cadres);
        $meta = new MetaPaginateResource($cadres);

        return $this->respondSuccessWithMeta($data, $meta);
    }

    public function store(StoreCadreRequest $request): JsonResponse
    {
        try {
            $new_cadre = Cadre::create($request->validated());

            return $this->respondCreated(new CadreResource($new_cadre), 'Kader berhasil ditambahkan.');
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function show(Cadre $cadre): JsonResponse
    {
        return $this->respondSuccess(new CadreResource($cadre));
    }

    public function update(UpdateCadreRequest $request, Cadre $cadre): JsonResponse
    {
        try {
            $cadre->update($request->validated());

            return $this->respondSuccess(new CadreResource($cadre), 'Kader berhasil diperbarui.');
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function destroy(Cadre $cadre): JsonResponse
    {
        try {
            $cadre->delete();

            return $this->respondSuccess(null, 'Kader berhasil dihapus.');
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function bulkDestroy(BulkDestroyCadreRequest $request): JsonResponse
    {
        try {
            $deleted_count = 0;
            $failed_count = 0;

            if ($request->boolean('select_all')) {
                $exclude_ids = $request->input('exclude_ids', []);
                $filters = $request->input('filters', []);

                $query = Cadre::query()
                    ->when(isset($filters['search']), function ($query) use ($filters) {
                        return $query->where('name', 'LIKE', "%{$filters['search']}%");
                    })
                    ->when(isset($filters['batch']), function ($query) use ($filters) {
                        return $query->where('batch', $filters['batch']);
                    })
                    ->when(isset($filters['status']), function ($query) use ($filters) {
                        return $query->where('status', $filters['status']);
                    })
                    ->whereNotIn('id', $exclude_ids);

                $cadres = $query->get();
            } else {
                $ids = $request->input('ids', []);
                $cadres = Cadre::whereIn('id', $ids)->get();
            }

            foreach ($cadres as $cadre) {
                try {
                    $cadre->delete();
                    $deleted_count++;
                } catch (\Throwable $th) {
                    $failed_count++;
                }
            }

            return $this->respondSuccess([
                'deleted_count' => $deleted_count,
                'failed_count' => $failed_count,
            ], "$deleted_count kader berhasil dihapus.");
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }
}
