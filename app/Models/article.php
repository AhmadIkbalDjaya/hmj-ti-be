<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'publish_at' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];
}
