<?php

namespace App\Observers;

use App\Models\Position;
use Illuminate\Support\Facades\Cache;

class PositionObserver
{
    public function created(Position $position): void
    {
        $this->clearOrganizationalStructureCache();
    }

    public function updated(Position $position): void
    {
        $this->clearOrganizationalStructureCache();
    }

    public function deleted(Position $position): void
    {
        $this->clearOrganizationalStructureCache();
    }

    private function clearOrganizationalStructureCache(): void
    {
        Cache::forget(Position::ORGANIZATIONAL_STRUCTURE_CACHE_KEY);
    }
}
