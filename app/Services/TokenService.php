<?php
namespace App\Services;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class TokenService
{
    public function ensureValidToken(SocialAccount $account)
    {
        if (now()->lessThan($account->expires_at)) {
            return;
        }

        switch ($account->provider) {
            case 'reddit':
                $this->refreshRedditToken($account);
                break;  
            case 'facebook':
                $this->refreshFacebookToken($account);
                break;
            default:
                throw new \Exception("Token refresh not implemented for: {$account->provider}");
           
            // Add more providers as needed
        }
    }

    public function isAccessTokenExpired(SocialAccount $account)
    {
        return now()->greaterThanOrEqualTo($account->expires_at);
    }
    
    protected function refreshRedditToken(SocialAccount $account)
    {
        $clientId = env('REDDIT_CLIENT_ID');
        $clientSecret = env('REDDIT_CLIENT_SECRET');
        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post('https://www.reddit.com/api/v1/access_token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $account->refresh_token,
        ]);

        if ($response->successful()) {
            $account->update([
                'access_token' => $response['access_token'],
                'expires_at' => now()->addHour(),
            ]);
        } else {
            throw new \Exception('Could not refresh Reddit token');
        }
    }

    protected function refreshFacebookToken(SocialAccount $account)
    {
        $appId = env('FACEBOOK_APP_ID');
        $appSecret = env('FACEBOOK_APP_SECRET');
    
        // Exchange short-lived token for long-lived token
        $response = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'fb_exchange_token' => $account->access_token,
        ]);
    
        if ($response->successful()) {
            $newToken = $response['access_token'];
    
            // Optional: get token expiration time
            $debug = Http::get('https://graph.facebook.com/debug_token', [
                'input_token' => $newToken,
                'access_token' => "$appId|$appSecret",
            ]);
    
            $expiresAt = now()->addDays(60); // Default fallback
            if ($debug->successful()) {
                $data = $debug['data'];
                if (isset($data['expires_at'])) {
                    $expiresAt = now()->setTimestamp($data['expires_at']);
                }
            }
    
            $account->update([
                'access_token' => $newToken,
                'expires_at' => $expiresAt,
            ]);
        } else {
            throw new \Exception('Could not refresh Facebook token: ' . $response->body());
        }
    }
    
    public function refreshAccessToken(SocialAccount $account)
    {
        switch ($account->provider) {
            case 'reddit':
                return $this->refreshRedditToken($account);
            // Add cases for other providers (like Facebook, Twitter, etc.)
            default:
                throw new \Exception('No token refresh method for provider: ' . $account->provider);
        }
    }

    
}
