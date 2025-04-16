<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class SocialAccountController extends Controller
{
    /**
     * Get all connected social media accounts for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json(['message' => 'User ID is required'], 400);
        }

        $user = User::find($userId);
        $socialAccount = SocialAccount::where('user_id', $userId)->get();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($socialAccount);
    }

    /**
     * Connect a new social media account.
     */
    public function connect(Request $request)
    {
        $request->validate([
            'provider' => 'required|string', // e.g., facebook, twitter
            'provider_id' => 'required|string', // Unique ID from the platform
            'username' => 'required|string',
            'access_token' => 'required|string',
        ]);

        $account = SocialAccount::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'provider_id' => $request->provider_id
            ],
            [
                'provider' => $request->provider,
                'username' => $request->username,
                'access_token' => $request->access_token,
                'is_deleted' => false,
            ]
        );

        return response()->json(['message' => 'Account connected!', 'account' => $account]);
    }

    /**
     * Soft delete (disconnect) a social media account.
     */
    public function disconnect($id)
    {
        $account = SocialAccount::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $account->update(['is_deleted' => true]);

        return response()->json(['message' => 'Account disconnected!']);
    }
}
