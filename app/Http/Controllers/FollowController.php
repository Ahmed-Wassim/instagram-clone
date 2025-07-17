<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(User $user)
    {
        $authUser = auth()->user();

        if ($authUser->id === $user->id) {
            return response()->json(['error' => 'You cannot follow yourself'], 400);
        }

        if ($authUser->isFollowing($user)) {
            return response()->json(['message' => 'Already following'], 400);
        }

        $authUser->followings()->attach($user->id);

        return response()->json(['message' => 'Followed successfully']);
    }

    public function unfollow(User $user)
    {
        auth()->user()->followings()->detach($user->id);

        return response()->json(['message' => 'Unfollowed successfully']);
    }

    public function followers(User $user)
    {
        return response()->json([
            'followers' => $user->followers()->select('users.id', 'users.name', 'users.username')->get()
        ]);
    }

    public function followings(User $user)
    {
        return response()->json([
            'followings' => $user->followings()->select('users.id', 'users.name', 'users.username')->get()
        ]);
    }

    public function status(User $user)
    {
        return response()->json([
            'is_following' => auth()->user()->isFollowing($user)
        ]);
    }
}
