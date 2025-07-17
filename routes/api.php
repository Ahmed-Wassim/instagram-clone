<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\CommentController;

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

Route::middleware('auth:api')->group(function () {
    Route::post('/posts/{post:slug}/like', [LikeController::class, 'like']);
    Route::delete('/posts/{post:slug}/unlike', [LikeController::class, 'unlike']);
    Route::get('/posts/{post:slug}/likes', [LikeController::class, 'likesCount']); //

    Route::post('/comments/{comment}/like', [LikeController::class, 'likeComment']);
    Route::delete('/comments/{comment}/unlike', [LikeController::class, 'unlikeComment']);
    Route::get('/comments/{comment}/likes', [LikeController::class, 'commentLikes']); //
});

Route::middleware('auth:api')->group(function () {
    Route::get('/posts/{post:slug}/comments', [CommentController::class, 'index']); //
    Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/users/{user:username}/follow', [FollowController::class, 'follow']);
    Route::delete('/users/{user:username}/unfollow', [FollowController::class, 'unfollow']);
    Route::get('/users/{user:username}/followers', [FollowController::class, 'followers']);
    Route::get('/users/{user:username}/followings', [FollowController::class, 'followings']);
    Route::get('/users/{user:username}/follow-status', [FollowController::class, 'status']);
});
