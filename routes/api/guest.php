<?php

use App\Http\Controllers\Guest\ArticleController;
use App\Http\Controllers\Guest\BusinessController;
use App\Http\Controllers\Guest\ComplaintController;
use App\Http\Controllers\Guest\OrganizationalStructureController;
use Illuminate\Support\Facades\Route;

Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{article}', [ArticleController::class, 'show']);
Route::get('business', [BusinessController::class, 'index']);
Route::post('complaints', [ComplaintController::class, 'store']);
Route::get('organizational-structure', [OrganizationalStructureController::class, 'index']);
