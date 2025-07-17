<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
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

    //comments

    public function likeComment(Comment $comment)
    {
        $user = auth()->user();

        if ($comment->isLikedBy($user)) {
            return response()->json(['message' => 'Already liked'], 400);
        }

        $comment->likes()->create([
            'user_id' => $user->id,
        ]);

        return response()->json(['message' => 'Comment liked']);
    }

    public function unlikeComment(Comment $comment)
    {
        $user = auth()->user();

        $comment->likes()->where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Comment unliked']);
    }

    public function commentLikes(Comment $comment)
    {
        return response()->json([
            'likes' => $comment->likes()->count(),
            'liked_by_user' => $comment->isLikedBy(auth()->user()),
        ]);
    }
}
