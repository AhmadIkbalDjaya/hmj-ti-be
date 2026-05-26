<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Resources\Guest\ArticleDetailResource;
use App\Http\Resources\Guest\ArticleResource;
use App\Http\Resources\MetaPaginateResource;
use App\Models\Article;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    use HttpResponses;

    public function index(PaginateSearchRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = $request->input('search', '');
        $is_featured = $request->input('is_featured', null);

        $articles = Article::query()
            ->select(['id', 'title', 'slug', 'content', 'publish_at', 'image', 'is_featured'])
            ->active()
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('title', 'LIKE', "%$search%")
                ->orWhere('content', 'LIKE', "%$search%")
            ))
            ->when(! is_null($is_featured), fn ($query) => $query->where('is_featured', $is_featured))
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $data = ArticleResource::collection($articles);
        $meta = new MetaPaginateResource($articles);

        return $this->respondSuccessWithMeta($data, $meta);
    }

    public function show(Article $article): JsonResponse
    {
        return $this->respondSuccess(new ArticleDetailResource($article));
    }
}
