<?php

namespace App\Http\Controllers;

use App\Models\Reel;
use Illuminate\Http\Request;
use App\Jobs\ProcessReelUpload;
use Illuminate\Support\Facades\Storage;

class ReelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime|max:20480',
            'caption' => 'nullable|string|max:255'
        ]);

        $path = $request->file('video')->store('temp-reels', 'public');

        // Dispatch job to process the video
        ProcessReelUpload::dispatch(auth()->id(), $path, $request->caption);

        return response()->json(['message' => 'Upload queued for processing'], 202);
    }

    public function index()
    {
        return response()->json(
            Reel::with('user:id,name,username')->latest()->paginate(10)
        );
    }

    /**
     * Show a single reel by ID.
     */
    public function show($id)
    {
        $reel = Reel::with('user:id,name,username')->findOrFail($id);

        return response()->json($reel);
    }

    /**
     * Delete a reel (only if owned by user).
     */
    public function destroy($id)
    {
        $reel = Reel::findOrFail($id);

        if ($reel->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete the video file from storage
        Storage::disk('public')->delete($reel->video);

        $reel->delete();

        return response()->json(['message' => 'Reel deleted']);
    }
}
