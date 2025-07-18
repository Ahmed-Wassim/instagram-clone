<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        if ($request->hasFile("avatar")) {
            $avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            'bio' => $request->bio,
            'website' => $request->website,
        ]);

        $user->image()->create([
            'url' => $avatar,
        ]);

        $token = Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => "User Created Successfully",
            'user' => $user,
            'authentication' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => Auth::user(),
        ]);
    }

    public function me()
    {
        return auth()->user();
    }
    public function user(string $username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $authUser = auth()->user();
        $authUserId = $authUser ? $authUser->id : null;

        // Create cache key that includes auth user ID to handle follow status
        $cacheKey = "user_profile:{$user->id}:auth:{$authUserId}";

        $userProfile = Cache::remember(
            $cacheKey,
            1800, // 30 minutes
            function () use ($user, $authUser) {
                // Load relationships with counts
                $user->load([
                    'posts' => function ($query) {
                        $query->where('status', 'published')
                            ->with(['images', 'likes', 'comments'])
                            ->orderBy('created_at', 'desc');
                    },
                    'image'
                ]);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'bio' => $user->bio,
                    'website' => $user->website,
                    'avatar' => $user->image?->url ? asset('storage/' . $user->image->url) : null,
                    'followers_count' => $user->followers()->count(),
                    'followings_count' => $user->followings()->count(),
                    'posts_count' => $user->posts()->where('status', 'published')->count(),
                    'is_followed' => $authUser ? $authUser->isFollowing($user) : false,
                    'posts' => $user->posts->map(function ($post) {
                        return [
                            'id' => $post->id,
                            'caption' => $post->caption,
                            'images' => $post->images->map(fn($img) => asset('storage/' . $img->url)),
                            'likes_count' => $post->likes->count(),
                            'comments_count' => $post->comments->count(),
                            'created_at' => $post->created_at->toDateTimeString(),
                        ];
                    }),
                ];
            }
        );

        return response()->json([
            'status' => 'success',
            'user' => $userProfile,
        ]);
    }

    /**
     * Clear user profile cache
     */
    public function clearUserProfileCache($userId): void
    {
        $redis = Redis::connection();
        $pattern = "user_profile:{$userId}:*";

        $keys = $redis->keys($pattern);
        if (!empty($keys)) {
            $redis->del($keys);
        }
    }

    /**
     * Clear profile cache when follow relationships change
     */
    public function clearProfileCacheOnFollowChange($targetUserId, $authUserId): void
    {
        // Clear cache for the target user's profile for all auth users
        $this->clearUserProfileCache($targetUserId);

        // Also clear cache for authenticated user's profile
        if ($authUserId) {
            $this->clearUserProfileCache($authUserId);
        }
    }


    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
