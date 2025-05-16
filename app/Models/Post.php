<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'posts'; // Specify the table name

    protected $fillable = [
        'user_id',           // The user who created the post
        'content',           // The post content (text, media URL, etc.)
        'media',             // JSON field for storing media URLs
        'metadata', 
        'scheduled_at',
        
    ];

    protected $casts = [
        'media' => 'array', // Ensure media is stored as JSON
        'metadata' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Post.php
    public function socialAccounts()
    {
        return $this->belongsToMany(SocialAccount::class, 'post_social_account', 'post_id', 'social_account_id')
        ->using(PostSocialAccount::class) // Using the pivot model
        ->withPivot('status', 'published_at', 'error_message')
        ->withTimestamps();
    }

}
