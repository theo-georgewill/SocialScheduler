<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Services\RedditService;
use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected $reddit;

    public function __construct(RedditService $reddit, FacebookService $facebook)
    {
        $this->reddit = $reddit;
        $this->facebook = $facebook;
    }

    /**
     * Get all scheduled or posted social media posts for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $request->input('user_id');
        $status = $request->input('status');
        $provider = $request->input('provider');

        $query = Post::where('user_id', $userId);

        $query->whereNotNull('scheduled_at'); 
        $query->with(['socialAccounts' => function ($q) use ($status, $provider) {
            if ($status !== "all") {
                $q->wherePivot('status', $status);
            }
        
            if ($provider !== "all") {
                $q->where('social_accounts.provider', $provider);
            }
        }]);
        
        if ($status !== "all") {
            $query->whereHas('socialAccounts', function ($q) use ($status) {
                $q->where('post_social_account.status', $status);
            });
        }
        
        if ($provider !== "all") {
            $query->whereHas('socialAccounts', function ($q) use ($provider) {
                $q->where('social_accounts.provider', $provider);
            });
        }
        

        $posts = $query -> get();
        return response()->json($posts);
    }



    /**
     * Store a new social media post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov,avi',
            'scheduled_at' => 'required|date',
            'social_account_ids' => 'nullable|array',
            'social_account_ids.*' => 'exists:social_accounts,id',
            'metadata' => 'nullable|array',
        ]);
    
        // Handle file uploads
        $mediaPaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $mediaPaths[] = $file->store('media', 'public');
            }
        }
    
        // Create the post
        $post = Post::create([
            'user_id' => $request->input('user_id'),
            'content' => $validated['content'],
            'media' => !empty($mediaPaths) ? $mediaPaths : null,
            'metadata' => $validated['metadata'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
        ]);
    
        // Attach social accounts with default pivot values
        $pivotData = [];
        foreach ($validated['social_account_ids'] as $id) {
            $pivotData[$id] = [
                'status' => 'scheduled',
                'published_at' => null,
                'error_message' => null,
            ];
        }
        $post->socialAccounts()->attach($pivotData);
    
        return response()->json([
            'message' => 'Post scheduled!',
            'post' => $post
        ]);
    }

    /**
     * Soft delete (remove) a scheduled post.
     */
    public function destroy($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $post->delete(); // soft delete automatically sets deleted_at

        return response()->json(['message' => 'Post deleted!'], 204);
    }


    public function postToReddit(Request $request)
    {
        $request->validate([
            'social_account_id' => 'required|exists:social_accounts,id',
            'title' => 'required|string|max:300',
            'subreddit' => 'required|string',
            'content' => 'nullable|string',
            'url' => 'nullable|url',
        ]);

        $account = SocialAccount::where('id', $request->social_account_id)
            ->where('provider', 'reddit')
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $postType = $request->url ? 'link' : 'self';

        $postData = [
            'sr' => $request->subreddit,
            'title' => $request->title,
            'kind' => $postType,
        ];

        if ($postType === 'link') {
            $postData['url'] = $request->url;
        } else {
            $postData['text'] = $request->content;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
            'User-Agent' => 'YourApp/1.0 by TTG',
        ])->post('https://oauth.reddit.com/api/submit', $postData);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Failed to post to Reddit',
                'details' => $response->json(),
            ], 400);
        }

        return response()->json([
            'message' => 'Post submitted successfully',
            'reddit_response' => $response->json(),
        ]);
    }

    /**
     * Publish a post to the correct platform based on its provider.
     */
    public function publish(Post $post)
    {
        $provider = $post->socialAccount->provider;

        try {
            $method = 'publishTo' . ucfirst($provider);
            if (method_exists($this, $method)) {
                $this->{$method}($post);

                // Update post status
                $post->update([
                    'status' => 'posted',
                    'posted_at' => now(),
                ]);

                return response()->json(['message' => 'Post published successfully.']);
            } else {
                throw new \Exception("No publishing method for provider: $provider");
            }
        } catch (\Exception $e) {
            Log::error('Failed to publish post: ' . $e->getMessage());

            // Update post status
            $post->update([
                'status' => 'failed',
            ]);

            return response()->json(['error' => 'Failed to publish post.'], 500);
        }
    }

    public function publishToReddit(Post $post)
    {
        // Retrieve all Reddit accounts linked to this post
        $redditAccounts = $post->socialAccounts->where('provider', 'reddit');

        if ($redditAccounts->isEmpty()) {
            return response()->json(['error' => 'No Reddit accounts linked to this post.'], 400);
        }

        try {
            // Loop through each Reddit account and post
            foreach ($redditAccounts as $account) {
                // Ensure the access token is valid and publish to Reddit
                $this->reddit->publish($post, $account);
            }
            $post->update(['status' => 'posted', 'posted_at' => now()]);
            return response()->json(['message' => 'Posted to Reddit']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $post->update(['status' => 'failed']);
            return response()->json(['error' => 'Reddit post failed'], 500);
        }
    }

    public function publishToFacebook(Post $post)
    {
        // Retrieve all Facebook accounts linked to this post
        $facebookAccounts = $post->socialAccounts->where('provider', 'facebook');

        if ($facebookAccounts->isEmpty()) {
            return response()->json(['error' => 'No Facebook accounts linked to this post.'], 400);
        }

        foreach ($facebookAccounts as $account) {
            try {
                // Ensure the access token is valid and publish to Facebook
                $this->facebook->publish($post, $account);

                $post->socialAccounts()->updateExistingPivot($account->id, [
                    'status' => 'published',
                    'published_at' => now(),
                    'error_message' => null,
                ]);
            } catch (\Exception $e) {
                \Log::error("Failed to publish to Facebook account ID {$account->id}: " . $e->getMessage());

                $post->socialAccounts()->updateExistingPivot($account->id, [
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        // Optionally: Update main post status based on all outcomes
        $hasFailures = $post->socialAccounts()
            ->wherePivot('status', 'failed')
            ->exists();

        $post->update([
            'status' => $hasFailures ? 'partially_posted' : 'posted',
            'posted_at' => now(),
        ]);

        return response()->json(['message' => $hasFailures ? 'Posted with some failures' : 'Posted to Facebook']);
    }


    /*
     *Publish a post to Reddit.
     
    protected function publishToReddit(Post $post)
    {
        $account = $post->socialAccount;

        if ($this->isAccessTokenExpired($account)) {
            $this->refreshAccessToken($account);
        }

        $metadata = $post->metadata;

        $subreddit = $metadata['subreddit'] ?? null;
        $title = $metadata['title'] ?? substr($post->content, 0, 300);

        if (!$subreddit || !$title) {
            throw new \Exception('Missing subreddit or title metadata for Reddit post.');
        }

        $this->postToRedditApi($account, $subreddit, $title, $post->content);
    }
    */

    /**
     * Post content to Reddit via API.
     */
    private function postToRedditApi(SocialAccount $account, $subreddit, $title, $content)
    {
        $url = 'https://oauth.reddit.com/api/submit';

        $response = Http::withToken($account->access_token)
            ->post($url, [
                'title' => $title,
                'sr' => $subreddit,
                'kind' => 'self',  // 'self' for text post
                'text' => $content,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Reddit API error: ' . $response->body());
        }
    }

    /**
     * Publish a post to Facebook.
     */
    protected function oldPublishToFacebook(Post $post)
    {
        $account = $post->socialAccount;

        $metadata = $post->metadata;

        $link = $metadata['link'] ?? null;

        if ($this->isAccessTokenExpired($account)) {
            $this->refreshAccessToken($account);
        }

        $url = 'https://graph.facebook.com/v18.0/me/feed';

        $payload = [
            'message' => $post->content,
            'access_token' => $account->access_token,
        ];

        if ($link) {
            $payload['link'] = $link;
        }

        $response = Http::post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception('Facebook API error: ' . $response->body());
        }
    }

    /**
     * Check if the access token has expired.
     */
    private function isAccessTokenExpired(SocialAccount $account)
    {
        return now()->greaterThanOrEqualTo($account->expires_at);
    }

    /**
     * Refresh the access token if expired.
     */
    private function refreshAccessToken(SocialAccount $account)
    {
        if ($account->provider === 'reddit') {
            $newAccessToken = $this->getNewAccessTokenFromReddit($account->refresh_token);
            $account->update([
                'access_token' => $newAccessToken,
                'expires_at' => now()->addHours(1),
            ]);
        }
        // Add refresh logic for Facebook if needed
    }

    /**
     * Get a new Reddit access token using the refresh token.
     */
    private function getNewAccessTokenFromReddit($refreshToken)
    {
        $response = Http::asForm()->post('https://www.reddit.com/api/v1/access_token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => env('REDDIT_CLIENT_ID'),
            'client_secret' => env('REDDIT_CLIENT_SECRET'),
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        throw new \Exception('Failed to refresh Reddit access token.');
    }
}
