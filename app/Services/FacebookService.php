<?php

namespace App\Services;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use App\Services\TokenService;

class FacebookService
{
    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function publish(Post $post, SocialAccount $account)
    {
        $this->tokenService->ensureValidToken($account);

        $metadata = $post->metadata ?? [];

        $isPage = $metadata['is_page'] ?? false;
        $targetId = $metadata['page_id'] ?? $account->provider_user_id;

        if (!$targetId) {
            throw new \Exception('Missing Facebook target ID (page_id or user_id)');
        }

        $content = $post->content ?? '';
        $mediaType = $metadata['media_type'] ?? null; // 'photo' or 'video'
        $mediaUrl = $metadata['media_url'] ?? null;

        $accessToken = $account->access_token;

        if ($isPage && $metadata['page_access_token'] ?? false) {
            $accessToken = $metadata['page_access_token']; // override with page access token
        }

        $endpoint = "https://graph.facebook.com/{$targetId}/";

        if ($mediaType === 'photo') {
            $endpoint .= 'photos';
        } elseif ($mediaType === 'video') {
            $endpoint .= 'videos';
        } else {
            $endpoint .= 'feed';
        }

        $payload = ['access_token' => $accessToken];

        if ($mediaType === 'photo') {
            $payload['url'] = $mediaUrl;
            $payload['caption'] = $content;
        } elseif ($mediaType === 'video') {
            $payload['file_url'] = $mediaUrl;
            $payload['description'] = $content;
        } else {
            $payload['message'] = $content;
        }

        $response = Http::post($endpoint, $payload);

        if ($response->successful()) {
            $post->socialAccounts()->updateExistingPivot($account->id, [
                'status' => 'published',
                'published_at' => now(),
                'error_message' => null,
            ]);
        } else {
            $post->socialAccounts()->updateExistingPivot($account->id, [
                'status' => 'failed',
                'error_message' => $response->body(),
            ]);
            throw new \Exception("Facebook API error: " . $response->body());
        }
    }

    public function fetchUserPages(SocialAccount $account): array
    {
        $this->tokenService->ensureValidToken($account);

        $response = Http::get('https://graph.facebook.com/v19.0/me/accounts', [
            'access_token' => $account->access_token,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch Facebook Pages: ' . $response->body());
        }

        $pages = $response->json()['data'] ?? [];

        $storedPages = [];

        foreach ($pages as $page) {
            $storedPages[] = [
                'page_id' => $page['id'],
                'page_name' => $page['name'],
                'page_access_token' => $page['access_token'],
            ];

            // Optionally: store as metadata on the SocialAccount model
            // Merge with existing metadata if needed
            $account->update([
                'metadata' => array_merge($account->metadata ?? [], [
                    'pages' => $storedPages
                ])
            ]);
        }

        return $storedPages;
    }

}
