<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReelController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\ReelLikeController;
use App\Http\Controllers\SavedPostController;
use App\Http\Controllers\ReelCommentController;
use App\Http\Controllers\NotificationController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout')->middleware('auth:api');
    Route::post('refresh', 'refresh');
    Route::get('me', 'me')->middleware('auth:api');
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

    Route::post('/posts/{id}/save', [SavedPostController::class, 'store']);
    Route::delete('/posts/{id}/unsave', [SavedPostController::class, 'destroy']);
    Route::get('/saved-posts', [SavedPostController::class, 'index']);

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

//feed, explore
Route::get('/feed', [FeedController::class, 'index'])->middleware('auth:api');

Route::get('/explore', [ExploreController::class, 'index'])->middleware('auth:api');

Route::middleware('auth:api')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread', [NotificationController::class, 'unread']);
    Route::post('/read/{id}', [NotificationController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
});

Route::get('/search', [SearchController::class, 'index']);

Route::middleware('auth:api')->group(function () {
    Route::post('/reels', [ReelController::class, 'store']);
    Route::get('/reels', [ReelController::class, 'index']);
    Route::get('/reels/{reel}', [ReelController::class, 'show']);
    Route::delete('/reels/{reel}', [ReelController::class, 'destroy']);
});


Route::middleware('auth:api')->group(function () {
    Route::post('/reels/{id}/like', [ReelLikeController::class, 'like']);
    Route::delete('/reels/{id}/unlike', [ReelLikeController::class, 'unlike']);

    Route::get('/reels/{id}/comments', [ReelCommentController::class, 'index']);
    Route::post('/reels/{id}/comments', [ReelCommentController::class, 'store']);
    Route::delete('/reels/{id}/comments/{commentId}', [ReelCommentController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/stories', [StoryController::class, 'store']);
    Route::get('/stories', [StoryController::class, 'index']);
});
