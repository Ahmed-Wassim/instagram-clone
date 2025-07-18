<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class SavedPostController extends Controller
{
    public function store($postId)
    {
        $user = auth()->user();
        $post = Post::findOrFail($postId);

        $user->savedPosts()->syncWithoutDetaching([$post->id]);

        return response()->json(['message' => 'Post saved']);
    }

    public function destroy($postId)
    {
        auth()->user()->savedPosts()->detach($postId);

        return response()->json(['message' => 'Post unsaved']);
    }

    public function index()
    {
        $posts = auth()->user()->savedPosts()
            ->with(['user:id,name,username', 'images'])
            ->latest()
            ->paginate(10);

        return response()->json($posts);
    }
}
