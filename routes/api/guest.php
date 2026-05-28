<?php

use App\Http\Controllers\Guest\AboutController;
use App\Http\Controllers\Guest\ArticleController;
use App\Http\Controllers\Guest\BusinessController;
use App\Http\Controllers\Guest\CadreController;
use App\Http\Controllers\Guest\ComplaintController;
use App\Http\Controllers\Guest\OrganizationalStructureController;
use Illuminate\Support\Facades\Route;

Route::get('about', [AboutController::class, 'show']);
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{article:slug}', [ArticleController::class, 'show']);
Route::get('businesses', [BusinessController::class, 'index']);
Route::post('complaints', [ComplaintController::class, 'store']);
Route::get('organizational-structure', [OrganizationalStructureController::class, 'index']);
Route::get('cadres', [CadreController::class, 'index']);
