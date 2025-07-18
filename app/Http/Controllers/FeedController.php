<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class FeedController extends Controller
{
    const FEED_CACHE_TTL = 1800; // 30 minutes

    public function index(Request $request)
    {
        $user = auth()->user();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        // Create cache key based on user's followed users and pagination
        $cacheKey = $this->getFeedCacheKey($user->id, $page, $perPage);

        $posts = Cache::remember(
            $cacheKey,
            self::FEED_CACHE_TTL,
            function () use ($user, $perPage) {
                $followingIds = $user->followings()->pluck('id')->toArray();

                $followingIds[] = $user->id;

                return Post::whereIn('user_id', $followingIds)
                    ->with([
                        'user:id,name,username',
                        'user.image', // avatar
                        'images',
                        'likes',
                        'comments',
                    ])
                    ->withCount(['likes', 'comments'])
                    ->where('status', 'published')
                    ->latest()
                    ->paginate($perPage);
            }
        );

        return response()->json($posts);
    }

    public function show($id)
    {
        $post = Post::with(['user:id,name,username', 'images'])->findOrFail($id);

        return response()->json([
            'post' => $post,
            'is_saved' => $post->isSavedBy(auth()->user())
        ]);
    }

    /**
     * Generate cache key for user's feed
     */
    private function getFeedCacheKey($userId, $page, $perPage): string
    {
        return "user_feed:{$userId}:{$page}:{$perPage}";
    }

    /**
     * Clear feed cache for a specific user
     */
    public function clearFeedCache($userId): void
    {
        $redis = Redis::connection();
        $pattern = "user_feed:{$userId}:*";

        $keys = $redis->keys($pattern);
        if (!empty($keys)) {
            $redis->del($keys);
        }
    }

    /**
     * Clear feed cache for all users who follow a specific user
     * This should be called when a user posts, updates, or deletes content
     */
    public static function clearFollowersFeedCache($userId): void
    {
        // Get all users who follow this user
        $followerIds = \App\Models\User::find($userId)
            ->followers()
            ->pluck('id')
            ->toArray();

        // Add the user themselves
        $followerIds[] = $userId;

        $redis = Redis::connection();

        foreach ($followerIds as $followerId) {
            $pattern = "user_feed:{$followerId}:*";
            $keys = $redis->keys($pattern);
            if (!empty($keys)) {
                $redis->del($keys);
            }
        }
    }

    /**
     * Clear feed cache when user follows/unfollows someone
     */
    public function clearFeedCacheOnFollowChange($userId): void
    {
        $this->clearFeedCache($userId);
    }
}
