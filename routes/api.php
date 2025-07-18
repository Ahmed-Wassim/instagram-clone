<?php

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


Route::middleware('auth:api')->group(function () {
    Route::post('/profile/{username}', [App\Http\Controllers\ProfileController::class, 'update']);


    Route::prefix('posts')->group(function () {
        //post controller
        Route::post('/', [PostController::class, 'store']);
        Route::delete('/{slug}', [PostController::class, 'destory']);
        Route::post('/{slug}', [PostController::class, 'update']);

        //like posts
        Route::post('/{post:slug}/like', [LikeController::class, 'like']);
        Route::delete('/{post:slug}/unlike', [LikeController::class, 'unlike']);
        Route::get('/{post:slug}/likes', [LikeController::class, 'likesCount']);

        //save posts, bookmarks+
        Route::post('/{id}/save', [SavedPostController::class, 'store']);
        Route::delete('/{id}/unsave', [SavedPostController::class, 'destroy']);

        //comment on posts
        Route::get('/{post:slug}/comments', [CommentController::class, 'index']);
        Route::post('/{post:slug}/comments', [CommentController::class, 'store']);
    });

    //saved posts
    Route::get('/saved-posts', [SavedPostController::class, 'index']);


    //like comments
    Route::prefix('comments')->group(function () {
        Route::post('/{comment}/like', [LikeController::class, 'likeComment']);
        Route::delete('/{comment}/unlike', [LikeController::class, 'unlikeComment']);
        Route::get('/{comment}/likes', [LikeController::class, 'commentLikes']);
        Route::delete('/{comment}', [CommentController::class, 'destroy']);
    });

    //follow system
    Route::prefix('users')->group(function () {
        Route::post('/{user:username}/follow', [FollowController::class, 'follow']);
        Route::delete('/{user:username}/unfollow', [FollowController::class, 'unfollow']);
        Route::get('/{user:username}/followers', [FollowController::class, 'followers']);
        Route::get('/{user:username}/followings', [FollowController::class, 'followings']);
        Route::get('/{user:username}/follow-status', [FollowController::class, 'status']);
    });

    //feed
    Route::get('/feed', [FeedController::class, 'index']);

    //explore
    Route::get('/explore', [ExploreController::class, 'index']);

    //notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/read/{id}', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    //search on users, posts
    Route::get('/search', [SearchController::class, 'index']);

    Route::prefix('reels')->group(function () {
        //reel
        Route::post('/', [ReelController::class, 'store']);
        Route::get('/', [ReelController::class, 'index']);
        Route::get('/{reel}', [ReelController::class, 'show']);
        Route::delete('/{reel}', [ReelController::class, 'destroy']);

        //like reels
        Route::post('/{id}/like', [ReelLikeController::class, 'like']);
        Route::delete('/{id}/unlike', [ReelLikeController::class, 'unlike']);

        // comment on reels
        Route::get('/{id}/comments', [ReelCommentController::class, 'index']);
        Route::post('/{id}/comments', [ReelCommentController::class, 'store']);
        Route::delete('/{id}/comments/{commentId}', [ReelCommentController::class, 'destroy']);
    });

    Route::prefix('stories')->group(function () {
        Route::post('/', [StoryController::class, 'store']);
        Route::get('/', [StoryController::class, 'index']);
    });
});
