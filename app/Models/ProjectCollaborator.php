<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCollaborator extends Model
{
    protected $table = 'project_collaborators';

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'joined_at'
        // If you want a 'status' here, add it. E.g. 'status' => 'pending'
    ];

    public $timestamps = false; // if you're only using joined_at

    public function project()
    {
        return $this->belongsTo(CollaborativeProject::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
