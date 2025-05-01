<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'social_accounts'; // Explicit table name

    protected $fillable = [
        'user_id',         // The owner of the social account
        'provider',        // The platform (e.g., 'facebook', 'twitter')
        'provider_id',     // Unique ID from the provider (e.g., Facebook user ID)
        'username',        // The user's social media username
        'access_token',           // Access token for API requests
        'refresh_token',     // Refresh token (for refreshing expired tokens)
        'expires_at',// Token expiration date
        'is_deleted'       // Soft delete flag (false by default, true when "deleted")
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_deleted' => 'boolean'
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_social_account', 'social_account_id', 'post_id')
            ->using(PostSocialAccount::class)
            ->withTimestamps();
    }
}
