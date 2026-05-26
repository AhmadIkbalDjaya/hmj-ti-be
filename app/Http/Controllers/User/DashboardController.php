<?php

namespace App\Http\Controllers\User;

use App\Enums\CadreStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Business;
use App\Models\Cadre;
use App\Models\Complaint;
use App\Models\Member;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use HttpResponses;

    public function summary(): JsonResponse
    {
        $data = [
            'articles' => [
                'total' => Article::count(),
                'active' => Article::where('is_active', true)->count(),
            ],
            'businesses' => [
                'total' => Business::count(),
                'active' => Business::where('is_active', true)->count(),
            ],
            'members' => [
                'total' => Member::count(),
            ],
            'complaints' => [
                'total' => Complaint::count(),
                'unread' => Complaint::unread()->count(),
            ],
            'cadres' => [
                'total' => Cadre::count(),
                'active' => Cadre::where('status', CadreStatus::ACTIVE)->count(),
            ],
        ];

        return $this->respondSuccess($data);
    }
}
