<?php

namespace App\Http\Controllers;

use App\Models\Reel;
use Illuminate\Http\Request;
use App\Notifications\ReelLiked;

class ReelLikeController extends Controller
{
    public function like($id)
    {
        $reel = Reel::findOrFail($id);
        $reel->likes()->firstOrCreate([
            'user_id' => auth()->id()
        ]);

        if ($reel->user_id !== auth()->id()) {
            $reel->user->notify(new ReelLiked($reel, auth()->user()));
        }

        return response()->json(['message' => 'Reel liked']);
    }

    public function unlike($id)
    {
        $reel = Reel::findOrFail($id);
        $reel->likes()->where('user_id', auth()->id())->delete();

        return response()->json(['message' => 'Reel unliked']);
    }
}
