<?php

use App\Http\Controllers\User\ArticleController;
use App\Http\Controllers\User\AuthenticationController;
use App\Http\Controllers\User\BusinessController;
use App\Http\Controllers\User\CadreController;
use App\Http\Controllers\User\ComplaintController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\MemberController;
use App\Http\Controllers\User\OrganizationProfileController;
use App\Http\Controllers\User\PositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {
    Route::get('dashboard/summary', [DashboardController::class, 'summary'])->middleware(['auth:sanctum']);

    Route::controller(AuthenticationController::class)->group(function () {
        Route::post('login', 'login');
        Route::get('logout', 'logout')->middleware(['auth:sanctum']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('organization-profile', [OrganizationProfileController::class, 'show']);
        Route::put('organization-profile', [OrganizationProfileController::class, 'update']);

        Route::delete('articles/bulk-destroy', [ArticleController::class, 'bulkDestroy']);
        Route::resource('articles', ArticleController::class)->except(['create', 'edit']);

        Route::delete('businesses/bulk-destroy', [BusinessController::class, 'bulkDestroy']);
        Route::resource('businesses', BusinessController::class)->except(['create', 'edit']);

        Route::delete('complaints/bulk-destroy', [ComplaintController::class, 'bulkDestroy']);
        Route::patch('complaints/{complaint}/toggle-read', [ComplaintController::class, 'toggleRead']);
        Route::resource('complaints', ComplaintController::class)->only(['index', 'show', 'destroy']);

        Route::resource('positions', PositionController::class)->except(['create', 'edit']);

        Route::delete('members/bulk-destroy', [MemberController::class, 'bulkDestroy']);
        Route::resource('members', MemberController::class)->except(['create', 'edit']);

        Route::delete('cadres/bulk-destroy', [CadreController::class, 'bulkDestroy']);
        Route::resource('cadres', CadreController::class)->except(['create', 'edit']);
    });
});
