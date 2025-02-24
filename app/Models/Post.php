<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts'; // Specify the table name

    protected $fillable = [
        'user_id',           // The user who created the post
        'social_account_id', // The connected social account used to post
        'content',           // The post content (text, media URL, etc.)
        'media',             // JSON field for storing media URLs
        'status',            // 'scheduled', 'posted', 'failed'
        'scheduled_at',      // When the post is scheduled to go live
        'posted_at',         // When the post was actually posted
        'is_deleted'         // Soft delete instead of actually deleting records
    ];

    protected $casts = [
        'media' => 'array', // Ensure media is stored as JSON
        'scheduled_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socialAccount()
    {
        return $this->belongsTo(SocialAccount::class);
    }
}
