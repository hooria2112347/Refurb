<?php 
// app/Models/CustomRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomRequest extends Model
{
    use HasFactory;

    protected $table = 'custom_requests';
    protected $fillable = [
        'user_id',
        'description',
        'materials',
        'dimensions',
        'style_preferences',
        'budget',
        'deadline',
        'artist_expertise',
        'status', // Ensure status is fillable
        'artist_id',
    ];

    /**
     * Get the images associated with the custom request.
     */
    public function images()
    {
        return $this->hasMany(CustomRequestImage::class);
    }

    /**
     * Get the user that owns the custom request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments associated with the custom request.
     */
    public function comments()
    {
        return $this->hasMany(CustomRequestComment::class);
    }

public function artist()
{
    return $this->belongsTo(User::class, 'artist_id'); // Relationship with the artist (user who accepted the request)
}
}