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
use Illuminate\Support\Facades\Storage;

class SocialAuthController extends Controller
{
    // Handle Facebook Redirect
    public function redirectToReddit(Request $request)
    {
        $state = Str::random(40); // Generate a random state token
        $userId = $request->input('user_id'); // Can be null

        // Store the state and user info in cache (user can be null)
        Cache::put('oauth_state_' . $state, [
            'exists' => true,
            'user_id' => $userId
        ], now()->addMinutes(10));

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
        
    // Handle Reddit Callback
    public function handleRedditCallback(Request $request)
    {
        // Validate the state parameter to prevent CSRF attacks
        $state = $request->input('state');
        $cachedState = Cache::pull('oauth_state_' . $state);

        if (!$state || !$cachedState) {
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

        try {
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
            $refreshToken = $data['refresh_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 3600;
            $expiresAt = now()->addSeconds($expiresIn);

            if (!$accessToken) {
                return response()->json(['error' => 'No access token received'], 400);
            }

            // Fetch Reddit user data
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'User-Agent'    => 'Autobuzz/1.0 by TTG',
            ])->get('https://oauth.reddit.com/api/v1/me');
            
            // Retry logic if rate-limited
            if ($userResponse->status() == 429) {
                return response()->json(['error' => 'Rate limit exceeded, please try again later.'], 429);
            }

            if ($userResponse->failed()) {
                return response()->json(['error' => 'Failed to fetch user details', 'details' => $userResponse->json()], 400);
            }

            $redditUser = $userResponse->json();

            // Download and store the avatar image
            $avatarUrl = $redditUser['icon_img'] ?? null;
            $avatarPath = null;

            if ($avatarUrl) {
                // Download the image
                $imageContent = file_get_contents($avatarUrl);

                if ($imageContent) {
                    // Create a unique name for the image
                    $imageName = 'avatar_' . uniqid() . '.jpg';

                    // Store the image in the 'avatars' directory
                    $avatarPath = 'avatars/' . $imageName;

                    // Store the file
                    Storage::disk('public')->put($avatarPath, $imageContent);
                }
            }

            // Get the user JSON from cache to check if it exists
            $cachedUserID = $cachedState['user_id'] ?? null;

            if ($cachedUserID) {
                // CONNECT flow: User is already registered, we just add the social account
                $user = User::findOrFail($cachedUserID); // Use the user ID from the cache

                // Check if the Reddit account is already connected to the user
                $socialAccount = SocialAccount::withTrashed()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'provider' => 'reddit',
                    ],
                    [
                        'provider_id' => $redditUser['id'],
                        'username' => $redditUser['name'],
                        'access_token' => $accessToken,
                        'refresh_token' => $refreshToken,
                        'expires_at' => $expiresAt,
                        'avatar' => $avatarPath,
                    ]
                );
                
                // Check if the account was soft deleted
                if ($socialAccount->trashed()) {
                    // Restore the soft-deleted account if it exists
                    $socialAccount->restore();
                }
                
                // Redirect to Vue app with token in the URL
                return redirect()->to("/social-account-callback");

            } else {
                // LOGIN/REGISTRATION flow: User doesn't exist, create a new one
                $user = User::updateOrCreate(
                    ['email' => $redditUser['id'] . '@reddit.com'], // Use Reddit ID as email
                    [
                        'name'        => $redditUser['name'],
                        'password'    => bcrypt(Str::random(16)),
                        'avatar'      => $redditUser['icon_img'] ?? null,
                    ]
                );

                // Create a new social account linked to the newly created user
                $socialAccount = SocialAccount::create([
                    'user_id' => $user->id,
                    'provider' => 'reddit',
                    'provider_id' => $redditUser['id'],
                    'username' => $redditUser['name'],
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'expires_at' => $expiresAt,
                    'avatar' => $avatarPath,
                ]);

                // Generate API token for the new user
                $token = $user->createToken('reddit-auth-token')->plainTextToken;

                // Redirect to Vue app with token in the URL
                return redirect()->to("/auth/callback?provider=reddit&token=$token&accessToken=$code&user=" . urlencode(json_encode($user)));

            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred during the process', 'details' => $e->getMessage()], 500);
        }
    }

    // Handle Facebook Redirect
    public function redirectToFacebook()
    {
    
        return response()->json([
            'url' => Socialite::driver('facebook')
            ->stateless()
            ->redirect()
            ->getTargetUrl()
        ]);
    }

    //Handle Facebook Callback
    public function handleFacebookCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('facebook')->stateless()->user();

            // Extract required data
            $accessToken = $socialUser->token;
            $refreshToken = $socialUser->refreshToken ?? null; // Facebook may not provide this
            $expiresIn = $socialUser->expiresIn ?? null; // Expiry time in seconds
            $expiresAt = $expiresIn ? now()->addSeconds($expiresIn) : null;

            // Fetch the Facebook profile picture using the Graph API
            $fbGraphUrl = "https://graph.facebook.com/{$socialUser->getId()}/picture?type=large&access_token={$accessToken}";
            $imageContent = file_get_contents($fbGraphUrl); // Fetch the image content from Facebook API

            $avatarPath = null;

            if ($imageContent) {
                // Create a unique name for the image
                $imageName = 'avatar_' . uniqid() . '.jpg';

                // Store the image in the 'avatars' directory in public storage
                $avatarPath = 'avatars/' . $imageName;

                // Store the file using Laravel's Storage facade
                Storage::disk('public')->put($avatarPath, $imageContent);
            }

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName(),
                    'password' => bcrypt(Str::random(16)),
                    'avatar' => $avatarPath ? $avatarPath : null,
                ]
            );

            // Generate a unique username
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
                    'avatar' => $avatarPath ? $avatarPath : null, 
                ]
            );


            $token = $user->createToken('auth-token')->plainTextToken;

            Auth::login($user);
            
            // Redirect to Vue app with token in the URL
            return redirect()->to("/auth/callback?provider=facebook&token=$token&accessToken=$accessToken&user=" . urlencode(json_encode($user)));

        } catch (\Exception $e) {
            return response()->json(['error' => 'Facebook authentication failed', 'message' => $e->getMessage()], 401);
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

    public function redirectToProvider($provider)
    {    
        return response()->json([
            'url' => Socialite::driver($provider)
            ->stateless()
            ->redirect()
            ->getTargetUrl()
        ]);
    }

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
