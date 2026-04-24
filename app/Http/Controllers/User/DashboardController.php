<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Business;
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
            ],
            'businesses' => [
                'total' => Business::count(),
            ],
            'members' => [
                'total' => Member::count(),
            ],
            'complaints' => [
                'total' => Complaint::count(),
            ],
        ];

        return $this->respondSuccess($data);
    }
}
