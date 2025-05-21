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
        'password',
        'phone',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Add this relationship
    public function products()
    {
        return $this->hasMany(Product::class, 'user_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }
    
    public function cart()
    {
        return $this->hasMany(Cart::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id', 'id');
    }


    /**
     * Get the portfolio projects for this user.
     */
    public function portfolioProjects()
    {
        return $this->hasMany(PortfolioProject::class);
    }

    /**
     * Get all completed projects that this user collaborated on.
     */
    public function completedProjects()
    {
        return $this->belongsToMany(CollaborativeProject::class, 'collaborator_project', 'user_id', 'project_id')
            ->where('status', 'completed');
    }
}
