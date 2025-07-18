<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Facades\Redis;

class Post extends Model
{
    use Sluggable;

    const CACHE_TTL = 3600;

    protected $with = ['images'];

    protected $fillable = [
        'user_id',
        "caption",
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'caption'
            ]
        ];
    }

    protected static function booted()
    {
        static::deleting(function ($post) {
            foreach ($post->images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }

            $post->clearCache();

            \App\Http\Controllers\FeedController::clearFollowersFeedCache($post->user_id);

            $post->user->clearProfileCache();
        });

        static::saved(function ($post) {
            // Clear cache when saving (creating/updating)
            $post->clearCache();

            \App\Http\Controllers\FeedController::clearFollowersFeedCache($post->user_id);

            $post->user->clearProfileCache();
        });
    }

    public function getCacheKey(): string
    {
        return "post:{$this->id}";
    }

    public function getSlugCacheKey(): string
    {
        return "post_slug:{$this->slug}";
    }

    public static function getUserPostsCacheKey($userId): string
    {
        return "user_posts:{$userId}";
    }

    public function clearCache(): void
    {
        Cache::forget($this->getCacheKey());
        Cache::forget($this->getSlugCacheKey());
        Cache::forget(self::getUserPostsCacheKey($this->user_id));

        // Clear paginated cache
        $this->clearPaginatedCache();
    }

    private function clearPaginatedCache(): void
    {
        $redis = Redis::connection();
        $patterns = [
            self::getAllPostsCacheKey() . ':*',
            self::getUserPostsCacheKey($this->user_id) . ':*'
        ];

        foreach ($patterns as $pattern) {
            $keys = $redis->keys($pattern);
            if (!empty($keys)) {
                $redis->del($keys);
            }
        }
    }

    // Static cache methods
    public static function findByIdCached($id)
    {
        return Cache::remember(
            "post:{$id}",
            self::CACHE_TTL,
            function () use ($id) {
                return self::with(['user', 'images', 'likes', 'comments.user'])
                    ->find($id);
            }
        );
    }

    public static function findBySlugCached($slug)
    {
        return Cache::remember(
            "post_slug:{$slug}",
            self::CACHE_TTL,
            function () use ($slug) {
                return self::with(['user', 'images', 'likes', 'comments.user'])
                    ->where('slug', $slug)
                    ->first();
            }
        );
    }

    public static function getUserPostsCached($userId, $perPage = 10)
    {
        $page = request()->get('page', 1);
        $cacheKey = self::getUserPostsCacheKey($userId) . ":{$perPage}:{$page}";

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($userId, $perPage) {
                return self::with(['user', 'images', 'likes'])
                    ->where('user_id', $userId)
                    ->where('status', 'published')
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
            }
        );
    }

    public static function getAllPostsCacheKey(): string
    {
        return "all_posts";
    }

    public static function getAllPostsCached($perPage = 15)
    {
        $page = request()->get('page', 1);
        $cacheKey = self::getAllPostsCacheKey() . ":{$perPage}:{$page}";

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($perPage) {
                return self::with(['user', 'images', 'likes'])
                    ->where('status', 'published')
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
            }
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function isSavedBy(User $user)
    {
        return $this->savedByUsers()->where('user_id', $user->id)->exists();
    }
}
