<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Requests\User\BulkDestroyComplaintRequest;
use App\Http\Requests\User\ToggleReadComplaintRequest;
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
            ->select(['id', 'name', 'email', 'phone', 'institute', 'description', 'is_read'])
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

    public function bulkDestroy(BulkDestroyComplaintRequest $request): JsonResponse
    {
        try {
            $deleted_count = 0;
            $failed_count = 0;

            if ($request->boolean('select_all')) {
                $exclude_ids = $request->input('exclude_ids', []);
                $filters = $request->input('filters', []);

                $query = Complaint::query()
                    ->when(isset($filters['search']), function ($query) use ($filters) {
                        $search = $filters['search'];

                        return $query->where(function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%$search%")
                                ->orWhere('description', 'LIKE', "%$search%");
                        });
                    })
                    ->whereNotIn('id', $exclude_ids);

                $complaints = $query->get();
            } else {
                $ids = $request->input('ids', []);
                $complaints = Complaint::whereIn('id', $ids)->get();
            }

            foreach ($complaints as $complaint) {
                try {
                    $complaint->delete();
                    $deleted_count++;
                } catch (\Throwable $th) {
                    $failed_count++;
                }
            }

            $message = "$deleted_count pengaduan berhasil dihapus.";

            return $this->respondSuccess([
                'deleted_count' => $deleted_count,
                'failed_count' => $failed_count,
            ], $message);
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function toggleRead(Complaint $complaint, ToggleReadComplaintRequest $request): JsonResponse
    {
        try {
            $is_read = $request->boolean('is_read');

            $complaint->update([
                'is_read' => $is_read,
                'read_at' => $is_read ? now() : null,
            ]);

            $baseMessage = 'Pesan ditandai sebagai';
            $message = $is_read ? "$baseMessage telah dibaca" : "$baseMessage belum dibaca";

            return $this->respondSuccess(new ComplaintDetailResource($complaint), $message);
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }
}
