<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    public function update(string $username, Request $request)
    {

        $user = auth()->user();

        if ($request->hasFile("avatar")) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar); // delete old photo
            }

            $avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'name' => $request->name ?? $user->name,
            'bio' => $request->bio ?? $user->bio,
            'website' => $request->website ?? $user->website,
            'avatar' => $avatar ?? $user->avatar,
        ]);

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }
}
