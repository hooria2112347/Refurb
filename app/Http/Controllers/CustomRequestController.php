<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\CustomRequest;

class CustomRequestController extends Controller
{
    // Show the form for creating a custom request
    public function create()
    {
            return view('welcome');
        
        
    }

    // Store the custom request data
    public function index(Request $request)
    {
        try {
            $customRequests = CustomRequest::where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->get();
    
            return response()->json(['data' => $customRequests], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch custom requests.'], 500);
        }
    }public function store(Request $request)
    {
        try {
            // Validate incoming request
            $validatedData = $request->validate([
                'description' => 'required|string|max:255',
                'materials' => 'nullable|string|max:255',
                'dimensions' => 'nullable|string|max:255',
                'style_preferences' => 'nullable|string|max:255',
                'budget' => 'nullable|numeric|min:0',
                'deadline' => 'nullable|date|after:today',
                'artist_expertise' => 'nullable|string|max:255',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
    
            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePaths[] = $image->store('uploads', 'public');
                }
            }
    
            // Save the custom request to the database
            CustomRequest::create([
                'user_id' => $request->user()->id, // Ensure user is authenticated
                'description' => $validatedData['description'],
                'materials' => $validatedData['materials'] ?? null,
                'dimensions' => $validatedData['dimensions'] ?? null,
                'style_preferences' => $validatedData['style_preferences'] ?? null,
                'budget' => $validatedData['budget'] ?? null,
                'deadline' => $validatedData['deadline'] ?? null,
                'artist_expertise' => $validatedData['artist_expertise'] ?? null,
                'images' => json_encode($imagePaths), // Convert image paths to JSON
                'status' => 'Pending', // Default status
            ]);
    
            return response()->json(['message' => 'Custom request created successfully!'], 201);
        } catch (\Exception $e) {
            \Log::error('Failed to create custom request: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create custom request.'], 500);
        }
    }
    

}