<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'rating',
        'comment'
    ];

    public function project()
    {
        return $this->belongsTo(CollaborativeProject::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}