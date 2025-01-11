<?php
// app/Models/CustomRequestComment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomRequestComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_request_id',
        'artist_id',
        'comment',
    ];

    /**
     * Get the custom request associated with the comment.
     */
    public function customRequest()
    {
        return $this->belongsTo(CustomRequest::class, 'custom_request_id');
    }

    /**
     * Get the artist who made the comment.
     */
    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }
}
