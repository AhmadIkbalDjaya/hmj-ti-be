<?php

namespace App\Observers;

use App\Models\Member;
use App\Models\Position;
use Illuminate\Support\Facades\Cache;

class MemberObserver
{
    public function created(Member $member): void
    {
        $this->clearOrganizationalStructureCache();
    }

    public function updated(Member $member): void
    {
        $this->clearOrganizationalStructureCache();
    }

    public function deleted(Member $member): void
    {
        $this->clearOrganizationalStructureCache();
    }

    private function clearOrganizationalStructureCache(): void
    {
        Cache::forget(Position::ORGANIZATIONAL_STRUCTURE_CACHE_KEY);
    }
}
