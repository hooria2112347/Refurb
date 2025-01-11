<?php

namespace App\Http\Controllers;

use App\Models\CollaborativeProject;
use App\Models\ProjectCollaborator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Show a list of active projects for browsing/discovery.
     * Optionally implement search by category, skills, etc.
     */
    public function index(Request $request)
    {
        // Example: filter by 'status=active'
        // You can also check query params for filters
        $projects = CollaborativeProject::where('status', 'active')
            ->with('owner')
            ->get();

        return response()->json($projects);
    }

    /**
     * Create a new collaborative project (POST).
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string'
            // Add 'deadline' if you want that
        ]);

        $project = CollaborativeProject::create([
            'owner_id'   => Auth::id(), // The logged-in artist
            'title'      => $request->title,
            'description'=> $request->description,
            'status'     => 'active'
        ]);

        return response()->json([
            'message' => 'Project created successfully!',
            'project' => $project
        ], 201);
    }

    /**
     * Show details of a specific project, including collaborators.
     */
    public function show($id)
    {
        $project = CollaborativeProject::with([
            'owner',
            'collaborators.user' // Eager load the user who is collaborating
        ])->findOrFail($id);

        return response()->json($project);
    }

    /**
     * Mark a project as completed.
     * Then optionally add it to all collaborators' portfolios.
     */
    public function completeProject($id)
    {
        $project = CollaborativeProject::findOrFail($id);

        // Only the owner can mark as completed
        if ($project->owner_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->update(['status' => 'completed']);

        // If you have "portfolios" or want to store them, do it here:
        // e.g. add to each collaborator's portfolio

        return response()->json([
            'message' => 'Project marked as completed.',
            'project' => $project
        ]);
    }


    public function getAvailableArtists($id)
{
    // 1) Find the project
    $project = CollaborativeProject::findOrFail($id);
    
    // 2) Get the user IDs of existing collaborators
    $collaboratorIds = $project->collaborators()->pluck('user_id')->toArray();
    
    // Also exclude the project owner
    $collaboratorIds[] = $project->owner_id;

    // 3) Query users who are role='artist' and NOT in collaboratorIds
    $artists = \App\Models\User::where('role', 'artist')
        ->whereNotIn('id', $collaboratorIds)
        ->get();

    return response()->json($artists);
}

}
