<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout')->middleware('auth:api');
    Route::post('refresh', 'refresh');
    Route::get('/profile/{username}', 'user')->middleware('auth:api');
});

Route::post('/profile/{username}', [App\Http\Controllers\ProfileController::class, 'update'])->middleware('auth:api');

Route::post('/posts', [PostController::class, 'store'])->middleware('auth:api');
Route::delete('/posts/{slug}', [PostController::class, 'destory'])->middleware('auth:api');
Route::post('posts/{slug}', [PostController::class, 'update'])->middleware('auth:api');
