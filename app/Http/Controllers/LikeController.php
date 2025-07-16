<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function like(Post $post)
    {
        $user = auth()->user();

        if ($post->isLikedBy($user)) {
            return response()->json(['message' => 'Already liked'], 400);
        }

        $post->likes()->create([
            'user_id' => $user->id,
        ]);

        return response()->json(['message' => 'Post liked']);
    }

    public function unlike(Post $post)
    {
        $user = auth()->user();

        $post->likes()->where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Post unliked']);
    }

    public function likesCount(Post $post)
    {
        return response()->json([
            'likes' => $post->likes()->count(),
            'liked_by_user' => $post->isLikedBy(auth()->user()),
        ]);
    }
}
