<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;


class SocialAccountController extends Controller
{
    //Get all connected social media accounts for the authenticated user.
    public function index(Request $request)
    {
        //$userId = $request->query('user_id', auth()->id()); // Default to the authenticated user's ID if not provided

        $userId = $request->query('user_id');

        // Ensure the user exists
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Retrieve social accounts that are not soft deleted (trashed)
        $socialAccounts = SocialAccount::where('user_id', $userId)
            ->whereNull('deleted_at') // Filter out soft-deleted accounts
            ->get();

        return response()->json([
            'social_accounts' => $socialAccounts,
            'authenticated_user' => $user,
        ]);
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

    //Soft delete (disconnect) a social media account.
    public function disconnect($provider, $id, Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }

        // Find the social account that matches user, provider, and account ID
        $account = SocialAccount::where('id', $id)
            ->where('provider', $provider)
            ->where('user_id', $userId)
            ->first();

        if (!$account) {
            return response()->json(['error' => 'Social account not found'], 404);
        }

        // Soft delete the account 
        $account->delete();

        return response()->json(['message' => 'Social account disconnected successfully'], 200);
    }

}
