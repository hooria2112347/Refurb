<?php

namespace App\Http\Controllers;

use App\Models\CollaborativeProject;
use App\Models\ProjectCollaborator;
use Illuminate\Support\Facades\Auth;

class ArtistProfileController extends Controller
{
    /**
     * Return all projects where the current user is the owner.
     */
    public function getOwnedProjects()
    {
        $userId = Auth::id();
        // Eager load collaborators if you want
        $projects = CollaborativeProject::where('owner_id', $userId)
            ->with('collaborators.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($projects);
    }

    /**
     * Return all projects where the current user is a collaborator.
     */
    public function getCollaborationProjects()
    {
        $userId = Auth::id();

        // First, find all collaborator records
        $collabRecords = ProjectCollaborator::where('user_id', $userId)->pluck('project_id');
        
        // Then, load those projects
        $projects = CollaborativeProject::whereIn('id', $collabRecords)
            ->with('owner', 'collaborators.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($projects);
    }
}
