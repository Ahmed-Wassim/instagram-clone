<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpeg,png,jpg,mp4,mov|max:20480',
        ]);

        $file = $request->file('media');
        $mime = $file->getMimeType();

        $isVideo = str_starts_with($mime, 'video');

        if ($isVideo) {
            $tempPath = $file->store('temp-stories', 'public');

            ProcessStoryVideo::dispatch(auth()->id(), $tempPath);

            return response()->json(['message' => 'Video story upload is being processed'], 202);
        } else {
            $path = $file->store('stories', 'public');

            Story::create([
                'user_id' => auth()->id(),
                'media' => $path,
                'type' => 'image',
            ]);

            return response()->json(['message' => 'Image story uploaded']);
        }
    }

    public function index()
    {
        $user = auth()->user();

        // Get stories from followed users (and self), posted within last 24h
        $stories = Story::with('user:id,name,username')
            ->whereIn('user_id', $user->followings()->pluck('id')->push($user->id))
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->get();

        return response()->json($stories);
    }
}
