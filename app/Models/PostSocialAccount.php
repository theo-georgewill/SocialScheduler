<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PostSocialAccount extends Pivot
{
    protected $table = 'post_social_account';

    protected $fillable = [
        'post_id',
        'social_account_id',
        'status',
        'published_at',
        'error_message',
    ];

    // You can add any custom methods or logic specific to this pivot relationship


}
