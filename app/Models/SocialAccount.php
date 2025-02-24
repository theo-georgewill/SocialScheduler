<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasFactory;

    protected $table = 'social_accounts'; // Explicit table name

    protected $fillable = [
        'user_id',         // The owner of the social account
        'provider',        // The platform (e.g., 'facebook', 'twitter')
        'provider_id',     // Unique ID from the provider (e.g., Facebook user ID)
        'username',        // The user's social media username
        'token',           // Access token for API requests
        'token_expires_at',// Token expiration date
        'is_deleted'       // Soft delete flag (false by default, true when "deleted")
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_deleted' => 'boolean'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
