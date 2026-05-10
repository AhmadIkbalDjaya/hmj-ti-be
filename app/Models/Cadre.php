<?php

namespace App\Models;

use App\Enums\CadreStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cadre extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => CadreStatus::class,
    ];
}
