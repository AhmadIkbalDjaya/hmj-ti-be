<?php

namespace App\Models;

use App\Observers\PositionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([PositionObserver::class])]
class Position extends Model
{
    use HasFactory;

    public const ORGANIZATIONAL_STRUCTURE_CACHE_KEY = 'guest.organizational-structure';

    protected $guarded = ['id'];

    protected $casts = [
        'level' => 'integer',
        'order_index' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Position::class, 'parent_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
