<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\Post;
use App\Services\TokenService;
use Illuminate\Support\Facades\Http;

class RedditService
{
    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function publish(Post $post, SocialAccount $account)
    {
        $this->tokenService->ensureValidToken($account);

        $metadata = $post->metadata;
        //$subreddit = $metadata['subreddit'] ?? null;
        $subreddit = 'test';
        //$title = $metadata['title'] ?? substr($post->content, 0, 300);
        $title = 'test title';

        if (!$subreddit || !$title) {
            throw new \Exception('Subreddit or title missing');
        }

        $response = Http::withToken($account->access_token)
            ->post('https://oauth.reddit.com/api/submit', [
                'sr' => $subreddit,
                'kind' => 'self',
                'title' => $title,
                'text' => $post->content,
            ]);

            $post->socialAccounts()->updateExistingPivot($account->id, [
                'status' => 'published',
                'published_at' => now(),
                'error_message' => null,
            ]);

        if ($response->failed()) {
            throw new \Exception('Reddit API error: ' . $response->body());
        }
    }

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

     // Create a Reddit post
     public function createPost(SocialAccount $socialAccount, $subreddit, $title, $content)
    {
        // Ensure that the access token exists in the SocialAccount model
        if (!$socialAccount->access_token) {
            throw new \Exception('Access token not found for this Reddit account.');
        }

        // Send request to create the post
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $socialAccount->access_token,
        ])->post('https://oauth.reddit.com/api/submit', [
            'title' => $title,
            'url' => $content, // For link posts; if content is text-based, use 'text' instead of 'url'
            'sr' => $subreddit, // Subreddit name
            'kind' => 'self', // For a text post, use 'self'; for a link, use 'link'
            'resubmit' => 'true',
        ]);

        return $response->json();
    }

    
}
