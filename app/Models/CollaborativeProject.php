<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollaborativeProject extends Model
{
    protected $table = 'collaborative_projects';

    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'status', // 'active' or 'completed'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Link to project_collaborators for accepted collaborators
    public function collaborators()
    {
        return $this->hasMany(ProjectCollaborator::class, 'project_id');
    }

    // Optional: If you have an "invitations" table
    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'project_id');
    }
}
