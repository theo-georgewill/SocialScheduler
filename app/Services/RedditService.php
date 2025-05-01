<?php

namespace App\Services;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;

class RedditService
{
    public function post(SocialAccount $account, array $data)
    {
        $payload = [
            'title' => $data['title'],
            'sr' => $data['subreddit'],
            'kind' => $data['kind'] ?? 'self', // 'self' = text post, 'link' = link post
        ];

        if ($payload['kind'] === 'self') {
            $payload['text'] = $data['content'] ?? '';
        } else {
            $payload['url'] = $data['url'] ?? '';
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
            'User-Agent' => 'YourApp/1.0 by TTG',
        ])->post('https://oauth.reddit.com/api/submit', $payload);

        if (!$response->successful()) {
            throw new \Exception('Failed to post to Reddit: ' . $response->body());
        }

        return $response->json();
    }

    public function refreshAccessToken(SocialAccount $account)
    {
        $response = Http::asForm()->post('https://www.reddit.com/api/v1/access_token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $account->refresh_token,
            'client_id' => env('REDDIT_CLIENT_ID'),
            'client_secret' => env('REDDIT_CLIENT_SECRET'),
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to refresh Reddit token: ' . $response->body());
        }

        $data = $response->json();
        $account->update([
            'access_token' => $data['access_token'],
            'expires_at' => now()->addSeconds($data['expires_in']),
        ]);
    }
}
