<?php

namespace App\Http\Controllers;

use App\Models\Reel;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Notifications\ReelCommented;

class ReelCommentController extends Controller
{
    public function index($id)
    {
        $reel = Reel::findOrFail($id);
        $comments = $reel->comments()->with('user:id,name,username')->latest()->get();

        return response()->json($comments);
    }

    public function store(Request $request, $id)
    {
        $request->validate(['body' => 'required|string']);

        $reel = Reel::findOrFail($id);

        $comment = $reel->comments()->create([
            'user_id' => auth()->id(),
            'body' => $request->body
        ]);

        if ($reel->user_id !== auth()->id()) {
            $reel->user->notify(new ReelCommented($reel, auth()->user()));
        }

        return response()->json($comment);
    }

    public function destroy($reelId, $commentId)
    {
        $comment = Comment::where('id', $commentId)
            ->where('commentable_type', Reel::class)
            ->where('commentable_id', $reelId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
