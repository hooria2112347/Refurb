<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\CustomRequest;
use App\Models\CustomRequestImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CustomRequestController extends Controller
{
    /**
     * Show the form for creating a custom request.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('welcome');
    }

    /**
     * Display a listing of the custom requests for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Fetch custom requests along with their associated images
            $customRequests = CustomRequest::with('images')
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['data' => $customRequests], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch custom requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch custom requests.'], 500);
        }
    }

    /**
     * Store a newly created custom request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate incoming request data
            $validatedData = $request->validate([
                'description' => 'required|string|max:255',
                'materials' => 'nullable|string|max:255',
                'dimensions' => 'nullable|string|max:255',
                'style_preferences' => 'nullable|string|max:255',
                'budget' => 'nullable|numeric|min:0',
                'deadline' => 'nullable|date|after:today',
                'artist_expertise' => 'nullable|string|max:255',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Create the CustomRequest record without the 'images' field
            $customRequest = CustomRequest::create([
                'user_id' => $request->user()->id, // Ensure the user is authenticated
                'description' => $validatedData['description'],
                'materials' => $validatedData['materials'] ?? null,
                'dimensions' => $validatedData['dimensions'] ?? null,
                'style_preferences' => $validatedData['style_preferences'] ?? null,
                'budget' => $validatedData['budget'] ?? null,
                'deadline' => $validatedData['deadline'] ?? null,
                'artist_expertise' => $validatedData['artist_expertise'] ?? null,
                'status' => 'Pending', // Default status
            ]);

            // Handle image uploads and store them in 'custom_request_images' table
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    // Store the image in the 'public/custom_request_images' directory
                    $path = $image->store('custom_request_images', 'public');

                    // Generate a publicly accessible URL for the stored image
                    $url = Storage::url($path);

                    // Create a new CustomRequestImage record
                    CustomRequestImage::create([
                        'custom_request_id' => $customRequest->id,
                        'url' => $url,
                    ]);
                }
            }

            // Load images relationship for the response
            $customRequest->load('images');

            return response()->json([
                'message' => 'Custom request created successfully!',
                'data' => $customRequest
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create custom request: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create custom request.'], 500);
        }
    }
}
