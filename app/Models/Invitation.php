<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $table = 'invitations';

    protected $fillable = [
        'project_id',
        'inviter_id',
        'invitee_id',
        'status', // pending, accepted, rejected
    ];

    

    public function project()
    {
        return $this->belongsTo(CollaborativeProject::class, 'project_id');
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }
}
