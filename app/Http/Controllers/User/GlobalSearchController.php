<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\GlobalSearchRequest;
use App\Models\Article;
use App\Models\Business;
use App\Models\Cadre;
use App\Models\Complaint;
use App\Models\Member;
use App\Models\Position;
use App\Traits\HttpResponses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class GlobalSearchController extends Controller
{
    use HttpResponses;

    public function index(GlobalSearchRequest $request): JsonResponse
    {
        $search = trim($request->input('search', ''));
        $limit = (int) $request->input('limit', 5);

        if ($search === '') {
            return $this->respondSuccess([
                'query' => $search,
                'total' => 0,
                'groups' => [],
            ]);
        }

        $groups = collect([
            $this->articles($search, $limit),
            $this->businesses($search, $limit),
            $this->positions($search, $limit),
            $this->members($search, $limit),
            $this->complaints($search, $limit),
            $this->cadres($search, $limit),
        ])->filter(fn (array $group) => $group['count'] > 0)->values();

        return $this->respondSuccess([
            'query' => $search,
            'total' => $groups->sum('count'),
            'groups' => $groups,
        ]);
    }

    private function articles(string $search, int $limit): array
    {
        $results = Article::query()
            ->select(['id', 'title'])
            ->where(fn (Builder $query) => $query->where('title', 'LIKE', "%$search%")
                ->orWhere('content', 'LIKE', "%$search%")
            )
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Article $article) => $this->result($article->id, $article->title, 'Berita & Kegiatan', "/articles/{$article->id}"));

        return $this->group('articles', 'Berita & Kegiatan', '/articles', $results);
    }

    private function businesses(string $search, int $limit): array
    {
        $results = Business::query()
            ->select(['id', 'title'])
            ->where(fn (Builder $query) => $query->where('title', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%")
            )
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Business $business) => $this->result($business->id, $business->title, 'Ekonomi Kreatif', "/businesses/{$business->id}"));

        return $this->group('businesses', 'Ekonomi Kreatif', '/businesses', $results);
    }

    private function positions(string $search, int $limit): array
    {
        $results = Position::query()
            ->select(['id', 'name', 'level', 'order_index'])
            ->where('name', 'LIKE', "%$search%")
            ->orderBy('level', 'asc')
            ->orderBy('order_index', 'asc')
            ->limit($limit)
            ->get()
            ->map(fn (Position $position) => $this->result($position->id, $position->name, 'Jabatan', "/positions/{$position->id}"));

        return $this->group('positions', 'Jabatan', '/positions', $results);
    }

    private function members(string $search, int $limit): array
    {
        $results = Member::query()
            ->select(['id', 'name', 'position_id'])
            ->where('name', 'LIKE', "%$search%")
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Member $member) => $this->result($member->id, $member->name, 'Anggota', "/members/{$member->id}"));

        return $this->group('members', 'Anggota', '/members', $results);
    }

    private function complaints(string $search, int $limit): array
    {
        $results = Complaint::query()
            ->select(['id', 'name'])
            ->where(fn (Builder $query) => $query->where('name', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%")
            )
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Complaint $complaint) => $this->result($complaint->id, $complaint->name, 'Pesan & Masukan', "/complaints/{$complaint->id}"));

        return $this->group('complaints', 'Pesan & Masukan', '/complaints', $results);
    }

    private function cadres(string $search, int $limit): array
    {
        $results = Cadre::query()
            ->select(['id', 'name'])
            ->where('name', 'LIKE', "%$search%")
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Cadre $cadre) => $this->result($cadre->id, $cadre->name, 'Kader', "/cadres/{$cadre->id}"));

        return $this->group('cadres', 'Kader', '/cadres', $results);
    }

    private function group(string $type, string $label, string $route, Collection $results): array
    {
        return [
            'type' => $type,
            'label' => $label,
            'route' => $route,
            'count' => $results->count(),
            'results' => $results->values(),
        ];
    }

    private function result(int $id, string $title, string $subtitle, string $url): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'subtitle' => $subtitle,
            'url' => $url,
        ];
    }
}
