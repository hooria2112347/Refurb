<?php

namespace App\Http\Controllers;

use App\Models\CollaborativeProject;
use App\Models\PortfolioProject;
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
        $query = CollaborativeProject::with('owner');
        
        // Add search functionality
        if ($request->has('q') && !empty($request->q)) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('skills_required', 'like', "%{$searchTerm}%");
            });
        }
        
        $projects = $query->get();
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
            'required_roles'  => 'required|string|not_regex:/\d/',
            'skills_required' => 'required|string|not_regex:/\d/',
            'deadline'        => 'required|date',
            'budget'          => 'required|numeric'
        ], [
            'required_roles.not_regex' => 'Required roles should not contain numbers.',
            'skills_required.not_regex' => 'Skills required should not contain numbers.',
            'budget.numeric' => 'Budget must be a numeric value.'
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
     * Delete a project.
     */
    public function destroy($id)
    {
        $project = CollaborativeProject::findOrFail($id);
        
        // Check if user is authorized to delete the project
        if ($project->owner_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Remove the status check that was here before
        // The block that prevented deleting completed projects has been removed
        
        // Delete related records first (to handle foreign key constraints)
        // Remove collaborator relationships
        $project->collaborators()->delete();
        
        // Remove feedback if exists
        Feedback::where('project_id', $project->id)->delete();
        
        // Remove portfolio entries if any exist
        PortfolioProject::where('project_id', $project->id)->delete();
        
        // Finally delete the project
        $project->delete();
        
        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }

    /**
     * Mark a project as completed.
     */
    public function completeProject($id)
    {
        $project = CollaborativeProject::with('collaborators.user')->findOrFail($id);

        // Check if user is authorized to complete the project
        if ($project->owner_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Update project status to completed
        $project->update(['status' => 'completed']);

        // Add the project to all collaborators' portfolios automatically
        foreach ($project->collaborators as $collaborator) {
            PortfolioProject::create([
                'user_id' => $collaborator->user_id,
                'project_id' => $project->id,
                'role_in_project' => $collaborator->role ?? 'Collaborator',
            ]);
        }

        // Also add to the owner's portfolio if owner is an artist
        $owner = \App\Models\User::find($project->owner_id);
        if ($owner && $owner->role === 'artist') {
            PortfolioProject::create([
                'user_id' => $owner->id,
                'project_id' => $project->id,
                'role_in_project' => 'Project Owner',
                'featured' => true // Automatically feature your own projects
            ]);
        }

        return response()->json([
            'message' => 'Project marked as completed and added to portfolios.',
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
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string'
        ]);
        
        $project = CollaborativeProject::findOrFail($id);

        // If you only allow feedback for completed projects:
        if ($project->status !== 'completed') {
            return response()->json(['error' => 'Project not completed yet.'], 403);
        }

        // In ProjectController.php submitFeedback method
$feedback = Feedback::create([
    'project_id' => $project->id,
    'rating'     => $request->rating,
    'comment'    => $request->comment,
    'user_id'    => Auth::id(),
]);

// Load the user relationship before returning
$feedback->load('user:id,name');

return response()->json([
    'message'  => 'Feedback submitted successfully!',
    'feedback' => $feedback
], 201);
    }

    public function getFeedback($id)
    {
        // 1. Ensure the project exists
        $project = CollaborativeProject::findOrFail($id);

        // 2. Retrieve associated feedback with user information
        $feedback = Feedback::where('project_id', $project->id)
            ->with('user:id,name')
            ->get();

        // 3. Return as JSON
        return response()->json($feedback);
    }
}