<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with([
            'user:id,name,username',
            'user.image',
            'images',
        ])
            ->withCount(['likes', 'comments'])
            ->inRandomOrder()
            ->paginate(12);

        return response()->json($posts);
    }
}
