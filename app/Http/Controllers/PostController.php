<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'caption' => 'nullable|string|max:1000',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'caption' => $request->input('caption') ?? null,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $url = $photo->store('posts', 'public');

                $post->images()->create([
                    'url' => $url
                ]);
            }
        }

        return response()->json(['message' => 'Post created successfully', 'post' => $post->load('images')]);
    }

    public function update(Request $request, string $slug)
    {
        // Remove this after testing
        // dd($request->all());

        $validated = $request->validate([
            'caption' => 'sometimes|string|max:255',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_image_ids' => 'sometimes|array',
            'deleted_image_ids.*' => 'exists:images,id'
        ]);

        $post = Post::where('slug', $slug)->firstOrFail(); // Use firstOrFail for better error handling

        try {
            DB::beginTransaction();

            // Only update if caption is provided
            if ($request->has('caption')) {
                $post->update($request->only(['caption']));
            }

            if ($request->has('deleted_image_ids')) {
                $post->images()->whereIn('id', $request->deleted_image_ids)->delete();
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('posts', 'public');

                    $post->images()->create([
                        'url' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => $post->fresh()->load('images')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destory(string $slug)
    {
        $post = Post::where('slug', $slug)->first();
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }
}
