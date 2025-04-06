<?php

namespace App\Http\Controllers;

use App\Models\PortfolioProject;
use App\Models\User;
use App\Models\CollaborativeProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortfolioController extends Controller
{
    /**
     * Display all portfolio projects for the authenticated user.
     */
    public function index()
    {
        $portfolioProjects = PortfolioProject::where('user_id', Auth::id())
            ->with('project')
            ->get();

        return response()->json($portfolioProjects);
    }

    /**
     * Display a specific user's public portfolio.
     */
    public function showUserPortfolio($userId)
    {
        \Log::info('Accessing user portfolio for user ID: ' . $userId);
        
        $user = User::findOrFail($userId);
        $portfolioProjects = PortfolioProject::where('user_id', $userId)
            ->where('public', true)
            ->with('project')
            ->get();
            
        \Log::info('Found ' . count($portfolioProjects) . ' public projects');
        
        return response()->json([
            'user' => $user,
            'portfolio' => $portfolioProjects
        ]);
    }

    /**
     * Update portfolio project details like contribution description.
     */
    public function updatePortfolioProject(Request $request, $id)
    {
        $portfolioProject = PortfolioProject::findOrFail($id);
       
        // Ensure the user owns this portfolio entry
        if ($portfolioProject->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'artist_contribution' => 'nullable|string',
            'role_in_project' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'public' => 'nullable|boolean',
        ]);
        
        $portfolioProject->update($request->only([
            'artist_contribution',
            'role_in_project',
            'featured',
            'public',
        ]));
        
        return response()->json([
            'message' => 'Portfolio project updated successfully',
            'portfolioProject' => $portfolioProject
        ]);
    }

    /**
     * Display all completed projects with optional pagination.
     */
    public function completedProjects(Request $request)
    {
        $perPage = $request->get('per_page', 12);
        $page = $request->get('page', 1);
        
        $query = CollaborativeProject::where('status', 'completed')
            ->orderBy('updated_at', 'desc');
            
        // If pagination is requested
        if ($perPage > 0) {
            $completedProjects = $query->paginate($perPage);
            
            \Log::info('Found ' . $completedProjects->total() . ' completed projects, showing page ' . $page);
            
            return response()->json($completedProjects);
        } else {
            // Get all projects without pagination
            $completedProjects = $query->get();
            
            \Log::info('Found ' . count($completedProjects) . ' completed projects');
            
            return response()->json([
                'data' => $completedProjects
            ]);
        }
    }

    /**
     * Remove a project from the user's portfolio.
     */
    public function removeFromPortfolio($id)
    {
        $portfolioProject = PortfolioProject::findOrFail($id);
        
        // Ensure the user owns this portfolio entry
        if ($portfolioProject->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $portfolioProject->delete();

        return response()->json([
            'message' => 'Project removed from portfolio successfully'
        ]);
    }
}