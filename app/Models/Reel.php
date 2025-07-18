<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reel extends Model
{
    protected $fillable = ['user_id', 'video', 'caption'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->morphMany(\App\Models\Like::class, 'likeable');
    }

    public function comments()
    {
        return $this->morphMany(\App\Models\Comment::class, 'commentable');
    }

    public function isLikedBy($user)
    {
        return $user && $this->likes()->where('user_id', $user->id)->exists();
    }
}
