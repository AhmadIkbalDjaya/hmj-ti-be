<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Guest\OrganizationalStructureResource;
use App\Models\Position;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class OrganizationalStructureController extends Controller
{
    use HttpResponses;

    public function index(): JsonResponse
    {
        $positions = Cache::remember(
            Position::ORGANIZATIONAL_STRUCTURE_CACHE_KEY,
            now()->addDay(),
            fn () => Position::query()
                ->select(['id', 'name', 'slug', 'level', 'order_index'])
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->with([
                    'members:id,name,photo,position_id',
                    'children' => function ($query) {
                        $query->where('is_active', true)
                            ->with([
                                'members:id,name,photo,position_id',
                                'children' => function ($query) {
                                    $query->where('is_active', true)
                                        ->with('members:id,name,photo,position_id')
                                        ->orderBy('order_index');
                                }])
                            ->orderBy('order_index');
                    }])
                ->orderBy('order_index')
                ->get()
        );

        return $this->respondSuccess(OrganizationalStructureResource::collection($positions));
    }
}
