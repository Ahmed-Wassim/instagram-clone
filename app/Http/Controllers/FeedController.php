<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get posts from followed users
        $posts = Post::whereIn('user_id', $user->followings()->pluck('id'))
            ->with([
                'user:id,name,username',
                'user.image', // avatar
                'images',
                'likes',
                'comments',
            ])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(10); // optional pagination

        return response()->json($posts);
    }
}
