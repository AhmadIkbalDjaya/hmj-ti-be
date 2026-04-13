<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Guest\ArticleResource;
use App\Http\Resources\Guest\BusinessResource;
use App\Http\Resources\Guest\CarouselResource;
use App\Models\Article;
use App\Models\Business;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    use HttpResponses;

    public function carousel(): JsonResponse
    {
        $data = Article::where('is_featured', true)
            ->where('is_active', true)
            ->latest()
            ->get();

        return $this->respondSuccess(CarouselResource::collection($data));
    }

    public function article(): JsonResponse
    {
        $data = Article::where('is_active', true)
            ->latest()
            ->limit(3)
            ->get();

        return $this->respondSuccess(ArticleResource::collection($data));
    }

    public function business(): JsonResponse
    {
        $data = Business::where('is_active', true)
            ->latest()
            ->limit(2)
            ->get();

        return $this->respondSuccess(BusinessResource::collection($data));
    }
}
