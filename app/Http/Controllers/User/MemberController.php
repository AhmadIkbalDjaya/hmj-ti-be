<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateSearchRequest;
use App\Http\Requests\User\StoreMemberRequest;
use App\Http\Requests\User\UpdateMemberRequest;
use App\Http\Resources\MetaPaginateResource;
use App\Http\Resources\User\MemberDetailResource;
use App\Http\Resources\User\MemberResource;
use App\Models\Member;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    use HttpResponses;

    public function index(PaginateSearchRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $search = $request->input('search', '');
        $position_id = $request->input('position_id', null);

        $members = Member::query()
            ->select(['id', 'name', 'photo', 'position_id'])
            ->when($search, fn ($query) => $query->where('name', 'LIKE', "%$search%")
            )
            ->when($position_id, fn ($query) => $query->where('position_id', $position_id)
            )
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $data = MemberResource::collection($members);
        $meta = new MetaPaginateResource($members);

        return $this->respondSuccessWithMeta($data, $meta);
    }

    public function store(StoreMemberRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('members');
            }

            $new_member = Member::create($validated);

            return $this->respondCreated(new MemberDetailResource($new_member));
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function show(Member $member): JsonResponse
    {
        return $this->respondSuccess(new MemberDetailResource($member));
    }

    public function update(UpdateMemberRequest $request, Member $member): JsonResponse
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('members');
                if ($member->photo && Storage::exists($member->photo)) {
                    Storage::delete($member->photo);
                }
            } else {
                unset($validated['photo']);
            }

            $member->update($validated);

            return $this->respondSuccess(new MemberDetailResource($member));
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }

    public function destroy(Member $member): JsonResponse
    {
        try {
            if ($member->photo && Storage::exists($member->photo)) {
                Storage::delete($member->photo);
            }
            $member->delete();

            return $this->respondSuccess();
        } catch (\Throwable $th) {
            return $this->respondServerError($th);
        }
    }
}
