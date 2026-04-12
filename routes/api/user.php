<?php

use App\Http\Controllers\User\ArticleController;
use App\Http\Controllers\User\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {
    Route::controller(AuthenticationController::class)->group(function () {
        Route::post('login', 'login');
        Route::get('logout', 'logout')->middleware(['auth:sanctum']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::resource('articles', ArticleController::class)->except(['create', 'edit']);
    });
});
