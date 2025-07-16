<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Cviebrock\EloquentSluggable\Sluggable;

class Post extends Model
{
    use Sluggable;

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
        });
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
}
