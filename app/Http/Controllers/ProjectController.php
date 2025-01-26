<?php

namespace App\Http\Controllers;

use App\Models\CollaborativeProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Feedback;

class ProjectController extends Controller
{
    /**
     * Show a list of active projects for browsing/discovery.
     */
    public function index(Request $request)
    {
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
            'title'           => 'required|string',
            'description'     => 'nullable|string',
            'required_roles'  => 'required|string',
            'skills_required' => 'required|string',
            'deadline'        => 'required|date',
            'budget'          => 'nullable|numeric'
        ]);

        $project = CollaborativeProject::create([
            'owner_id'        => Auth::id(),
            'title'           => $request->title,
            'description'     => $request->description,
            'required_roles'  => $request->required_roles,
            'skills_required' => $request->skills_required,
            'deadline'        => $request->deadline,
            'budget'          => $request->budget,
            'status'          => 'active'
        ]);

        return response()->json([
            'message' => 'Project created successfully!',
            'project' => $project
        ], 201);
    }

    /**
     * Show project details.
     */
    public function show($id)
    {
        $project = CollaborativeProject::with([
            'owner',
            'collaborators.user'
        ])->findOrFail($id);

        return response()->json($project);
    }

    /**
     * Mark a project as completed.
     */
    public function completeProject($id)
    {
        $project = CollaborativeProject::findOrFail($id);

        if ($project->owner_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->update(['status' => 'completed']);

        return response()->json([
            'message' => 'Project marked as completed.',
            'project' => $project
        ]);
    }

    /**
     * Get available artists for collaboration excluding project owner and current collaborators.
     */
    public function getAvailableArtists($id)
    {
        $project = CollaborativeProject::findOrFail($id);
        $collaboratorIds = $project->collaborators()->pluck('user_id')->toArray();
        $collaboratorIds[] = $project->owner_id;

        $artists = \App\Models\User::where('role', 'artist')
            ->whereNotIn('id', $collaboratorIds)
            ->get();

        return response()->json($artists);
    }


    public function submitFeedback(Request $request, $id)
{
    // ... your validation ...
    $project = CollaborativeProject::findOrFail($id);

    // If you only allow feedback for completed projects:
    if ($project->status !== 'completed') {
        return response()->json(['error' => 'Project not completed yet.'], 403);
    }

    // Now insert user_id
    $feedback = \App\Models\Feedback::create([
        'project_id' => $project->id,
        'rating'     => $request->rating,
        'comment'    => $request->comment,
        'user_id'    => Auth::id(),  // <<--- store the authenticated user's ID
    ]);

    return response()->json([
        'message'  => 'Feedback submitted successfully!',
        'feedback' => $feedback
    ], 201);
}



public function getFeedback($id)
{
    // 1. Ensure the project exists (optional if you want a 404 if project doesn't exist)
    $project = CollaborativeProject::findOrFail($id);

    // 2. Retrieve associated feedback
    $feedback = \App\Models\Feedback::where('project_id', $project->id)->get();

    // 3. Return as JSON
    return response()->json($feedback);
}
}