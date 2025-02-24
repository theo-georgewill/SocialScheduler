<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostSchedule extends Model
{
    use HasFactory;

    protected $table = 'post_schedules'; // Explicit table name

    protected $fillable = [
        'user_id',           // The user scheduling the post
        'post_id',           // The post being scheduled
        'scheduled_at',      // The date & time the post is scheduled to be published
        'status',            // 'pending', 'posted', 'failed'
        'platform',          // The social media platform (e.g., 'facebook', 'twitter')
        'is_deleted'         // Soft delete flag (false by default, true when "deleted")
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
