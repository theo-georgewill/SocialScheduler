<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {    
        return response()->json([
            'url' => Socialite::driver($provider)
            ->stateless()
            ->redirect()
            ->getTargetUrl()
        ]);
    }

    public function redirectToReddit()
    {
        $state = Str::random(40); // Generate a random state token
        Cache::put('oauth_state_' . $state, true, now()->addMinutes(10)); // Store in cache

        $url = "https://www.reddit.com/api/v1/authorize?" . http_build_query([
            'client_id' => env('REDDIT_CLIENT_ID'),
            'response_type' => 'code',
            'state' => $state,
            'redirect_uri' => env('REDDIT_REDIRECT_URI'),
            'duration' => 'permanent', // 'temporary' for short-lived tokens
            'scope' => 'identity,read,submit', // Comma-separated scopes
        ]);

        return response()->json(['url' => $url]);
    }

    public function redirectToFacebook()
    {
    
        return response()->json([
            'url' => Socialite::driver('facebook')
            ->stateless()
            ->redirect()
            ->getTargetUrl()
        ]);
    }

    /**
     * Handle Facebook Callback
     */
    public function handleFacebookCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('facebook')->stateless()->user();

            // Extract required data
            $accessToken = $socialUser->token;
            $refreshToken = $socialUser->refreshToken ?? null; // Facebook may not provide this
            $expiresIn = $socialUser->expiresIn ?? null; // Expiry time in seconds
            $expiresAt = $expiresIn ? now()->addSeconds($expiresIn) : null;

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName(),
                    'password' => bcrypt(Str::random(16)),
                    'avatar' => $socialUser->getAvatar(),
                ]
            );

            // Generate a unique username if not provided
            $baseUsername = Str::slug($socialUser->getNickname() ?? $socialUser->getName(), '_');
            $username = $baseUsername;
            $count = 1;
            while (SocialAccount::where('username', $username)->exists()) {
                $username = $baseUsername . '_' . $count++;
            }

            // Find or create the social account
            $socialAccount = SocialAccount::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'provider' => 'facebook',
                ],
                [
                    'provider_id' => $socialUser->getId(),
                    'username' => $username, // Ensure username is never null
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'expires_at' => $expiresAt,
                ]
            );

            /*
            return response()->json([
                'user' => $user,
                'token' => $user->createToken('auth-token')->plainTextToken,
                'access_token' => $socialAccount->access_token,
            ]);
            */
            // Redirect to Vue app with token in the URL
            return redirect()->to("/auth/callback?token=$accessToken&user=" . urlencode(json_encode($user)));

        } catch (\Exception $e) {
            return response()->json(['error' => 'Facebook authentication failed', 'message' => $e->getMessage()], 401);
        }
    }


    /**
     * Handle Reddit Callback
     */
    public function handleRedditCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('reddit')->stateless()->user();

            // Store Reddit access token
            $accessToken = $socialUser->token;

            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName(),
                    'password' => bcrypt(Str::random(16)),
                    'provider' => 'reddit',
                    'provider_id' => $socialUser->getId(),
                    'provider_token' => $accessToken,
                    'avatar' => $socialUser->getAvatar(),
                ]
            );

            return response()->json([
                'user' => $user,
                'token' => $user->createToken('auth-token')->plainTextToken,
                'reddit_access_token' => $accessToken,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Reddit authentication failed'], 401);
        }
    }

    /*
    public function handleRedditCallback(Request $request)
    {
        // Validate the state parameter
        $state = $request->input('state');
        if (!$state || !Cache::pull('oauth_state_' . $state)) {
            return response()->json(['error' => 'Invalid state'], 400);
        }

        // Check for authorization errors
        if ($request->has('error')) {
            return response()->json(['error' => $request->input('error')], 400);
        }

        // Get the authorization code
        $code = $request->input('code');
        if (!$code) {
            return response()->json(['error' => 'No authorization code received'], 400);
        }

        // Exchange authorization code for an access token
        $clientId = env('REDDIT_CLIENT_ID');
        $clientSecret = env('REDDIT_CLIENT_SECRET');
        $redirectUri = env('REDDIT_REDIRECT_URI');

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://www.reddit.com/api/v1/access_token', [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $redirectUri,
            ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Failed to get access token',
                'details' => $response->json()
            ], 400);
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;

        if (!$accessToken) {
            return response()->json(['error' => 'No access token received'], 400);
        }

        // Fetch Reddit user data
        $userResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'User-Agent'    => 'YourApp/1.0 by YourRedditUsername',
        ])->get('https://oauth.reddit.com/api/v1/me');

        if ($userResponse->failed()) {
            return response()->json(['error' => 'Failed to fetch user details', 'details' => $userResponse->json()], 400);
        }

        $redditUser = $userResponse->json();

        // Find or create user
        $user = User::updateOrCreate(
            ['email' => $redditUser['id'] . '@reddit.com'], // Reddit doesn't provide email
            [
                'name'        => $redditUser['name'],
                'password'    => bcrypt(Str::random(16)),
                'provider'    => 'reddit',
                'provider_id' => $redditUser['id'],
                'avatar'      => $redditUser['icon_img'] ?? null,
            ]
        );

        // Generate API token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user'               => $user,
            'token'              => $token,
            'reddit_access_token' => $accessToken, // Needed for API requests
        ]);
    }

    public function handleFacebookCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('facebook')->stateless()->user();

            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName(),
                    'password' => bcrypt(Str::random(16)),
                    'provider' => 'facebook',
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]
            );

            // Generate API token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }
    }
*/
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName(),
                    'password' => bcrypt(Str::random(16)),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]
            );

            // Generate API token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }
    }

    /**
     * Redirect the user to the OAuth provider.
     */
    public function redirect($provider)
    {
        // Ensure the user is authenticated
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'You must be logged in to connect accounts.');
        }
    
        // Store the logged-in user's ID in the session
        session(['social_connect_user_id' => auth()->id()]);
    
        // Redirect to the OAuth provider
        return Socialite::driver($provider)->scopes(['identity', 'submit'])->redirect();
    }

    /**
     * Handle OAuth callback and store user credentials.
     */
    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
    
            // Retrieve the user ID from session (set in redirectToProvider)
            $userId = session('social_connect_user_id');
    
            if (!$userId) {
                return redirect('/dashboard')->with('error', 'Session expired. Please try again.');
            }
    
            // Find the authenticated user
            $user = User::find($userId);
    
            if (!$user) {
                return redirect('/dashboard')->with('error', 'User not found.');
            }
    
            // Store the social account details in the database
            $user->socialAccounts()->updateOrCreate(
                ['provider' => $provider, 'provider_id' => $socialUser->getId()],
                ['token' => $socialUser->token, 'username' => $socialUser->getName()]
            );
    
            return redirect('/dashboard')->with('success', ucfirst($provider) . ' account connected successfully!');
        } catch (\Exception $e) {
            return redirect('/dashboard')->with('error', 'Failed to connect account.');
        }
    }

}
