<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Guest\OrganizationalStructureResource;
use App\Models\Position;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class OrganizationalStructureController extends Controller
{
    use HttpResponses;

    public function index(): JsonResponse
    {
        $positions = Position::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['members', 'children' => function ($query) {
                $query->where('is_active', true)
                    ->with(['members', 'children' => function ($query) {
                        $query->where('is_active', true)
                            ->with('members')
                            ->orderBy('order_index');
                    }])
                    ->orderBy('order_index');
            }])
            ->orderBy('order_index')
            ->get();

        return $this->respondSuccess(OrganizationalStructureResource::collection($positions));
    }
}
