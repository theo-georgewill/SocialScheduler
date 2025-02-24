<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Get all scheduled or posted social media posts for the authenticated user.
     */
    public function index()
    {
        return response()->json(
            Post::where('user_id', Auth::id())->where('is_deleted', false)->get()
        );
    }

    /**
     * Store a new social media post (either scheduled or immediate).
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'scheduled_at' => 'nullable|date',
            'platforms' => 'required|array', // e.g., ['facebook', 'twitter']
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'scheduled_at' => $request->scheduled_at,
            'platforms' => json_encode($request->platforms),
            'status' => 'scheduled', // Default status is "scheduled"
            'is_deleted' => false,
        ]);

        return response()->json(['message' => 'Post scheduled!', 'post' => $post]);
    }

    /**
     * Soft delete (remove) a scheduled post.
     */
    public function destroy($id)
    {
        $post = Post::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $post->update(['is_deleted' => true]);

        return response()->json(['message' => 'Post deleted!']);
    }
}
