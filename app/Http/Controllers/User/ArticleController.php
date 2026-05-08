<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Requests\User\BulkDestroyArticleRequest;
use App\Http\Requests\User\StoreArticleRequest;
use App\Http\Requests\User\UpdateArticleRequest;
use App\Http\Resources\MetaPaginateResource;
use App\Http\Resources\User\ArticleDetailResource;
use App\Http\Resources\User\ArticleResource;
use App\Models\Article;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    use HttpResponses;

    public function index(PaginateSearchRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = $request->input('search', '');
        $is_active = $request->input('is_active', null);
        $is_featured = $request->input('is_featured', null);

        $articles = Article::query()
            ->select(['id', 'title', 'slug', 'publish_at', 'is_active', 'is_featured'])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('title', 'LIKE', "%$search%")
                ->orWhere('content', 'LIKE', "%$search%")
            )
            )
            ->when($is_active, fn ($query) => $query->active())
            ->when($is_featured, fn ($query) => $query->featured())
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $data = ArticleResource::collection($articles);
        $meta = new MetaPaginateResource($articles);

        return $this->respondSuccessWithMeta($data, $meta);
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['image'] = $request->file('image')->store('articles');
            $new_article = Article::create($validated);

            return $this->respondCreated(new ArticleDetailResource($new_article));
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function show(Article $article): JsonResponse
    {
        return $this->respondSuccess(new ArticleDetailResource($article));
    }

    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('articles');
                if ($article->image && Storage::exists($article->image)) {
                    Storage::delete($article->image);
                }
            } else {
                unset($validated['image']);
            }

            $article->update($validated);

            return $this->respondSuccess(new ArticleDetailResource($article));
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function destroy(Article $article): JsonResponse
    {
        try {
            if ($article->image && Storage::exists($article->image)) {
                Storage::delete($article->image);
            }
            $article->delete();

            return $this->respondSuccess();
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function bulkDestroy(BulkDestroyArticleRequest $request): JsonResponse
    {
        try {
            $deleted_count = 0;
            $failed_count = 0;

            if ($request->boolean('select_all')) {
                $exclude_ids = $request->input('exclude_ids', []);
                $filters = $request->input('filters', []);

                $query = Article::query()
                    ->when(isset($filters['search']), function ($query) use ($filters) {
                        $search = $filters['search'];

                        return $query->where(function ($query) use ($search) {
                            $query->where('title', 'LIKE', "%$search%")
                                ->orWhere('content', 'LIKE', "%$search%");
                        });
                    })
                    ->when(isset($filters['is_active']), function ($query) use ($filters) {
                        return $query->where('is_active', $filters['is_active']);
                    })
                    ->when(isset($filters['is_featured']), function ($query) use ($filters) {
                        return $query->where('is_featured', $filters['is_featured']);
                    })
                    ->whereNotIn('id', $exclude_ids);

                $articles = $query->get();
            } else {
                $ids = $request->input('ids', []);
                $articles = Article::whereIn('id', $ids)->get();
            }

            foreach ($articles as $article) {
                try {
                    if ($article->image && Storage::exists($article->image)) {
                        Storage::delete($article->image);
                    }
                    $article->delete();
                    $deleted_count++;
                } catch (\Throwable $th) {
                    $failed_count++;
                }
            }

            $message = "$deleted_count artikel berhasil dihapus.";

            return $this->respondSuccess([
                'deleted_count' => $deleted_count,
                'failed_count' => $failed_count,
            ], $message);
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }
}
