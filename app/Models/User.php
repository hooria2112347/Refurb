<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'total_rating',
        'rating_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'total_rating' => 'decimal:2',
        'rating_count' => 'integer',
    ];

    /**
     * Add a rating to this user
     */
    public function addRating($rating)
    {
        $this->total_rating += $rating;
        $this->rating_count += 1;
        $this->save();
        
        return $this;
    }

    /**
     * Get the average rating for this user
     */
    public function getAverageRatingAttribute()
    {
        if ($this->rating_count > 0) {
            return round($this->total_rating / $this->rating_count, 1);
        }
        return 0;
    }

    /**
     * Get products belonging to this user
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Check if user can rate another user
     */
    public function canRate($userId)
    {
        return $this->id != $userId;
    }
}