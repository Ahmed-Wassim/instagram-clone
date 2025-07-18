<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');

        if (! $query) {
            return response()->json(['error' => 'Query is required'], 422);
        }

        $users = User::where('username', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->select('id', 'name', 'username')
            ->limit(10)
            ->get();

        // Search posts by caption
        $posts = Post::where('caption', 'like', "%{$query}%")
            ->with(['user:id,name,username', 'images'])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'users' => $users,
            'posts' => $posts,
        ]);
    }
}
