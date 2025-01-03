<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\CustomRequest;
use App\Models\CustomRequestImage;
use App\Models\CustomRequestComment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CustomRequestController extends Controller {
    public function accept(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user(); // Get the logged-in user
    
            // Ensure the user is an artist
            if ($user->role !== 'artist') {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }
    
            // Find the custom request by ID
            $customRequest = CustomRequest::findOrFail($id);
    
            // Check if the request is still pending
            if ($customRequest->status !== 'Pending') {
                return response()->json(['error' => 'Request is not pending.'], 400);
            }
    
            // Update the status to 'Accepted' and set the artist's ID
            $customRequest->status = 'Accepted';  // Change the status to accepted
            $customRequest->artist_id = $user->id;  // Assign the artist's ID to the 'artist_id' column
            $customRequest->save();  // Save the changes to the database
    
            return response()->json(['message' => 'Custom request accepted successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $mnfe) {
            return response()->json(['error' => 'Custom request not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to accept custom request.'], 500);
        }
    }
    

public function decline($id)
{
    try {
        $customRequest = CustomRequest::findOrFail($id);
        $customRequest->status = 'Declined';
        $customRequest->save();

        return response()->json([
            'message' => 'Request declined successfully.',
        ]);
    } catch (\Exception $e) {
        Log::error('Error declining request: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to decline request.'], 500);
    }
}

    
    // Show a single custom request and its associated details like comments, artist, images
    public function show($id): JsonResponse
{
    try {
        // Fetch the custom request along with associated images, comments, artist, and user data
        $request = CustomRequest::with(['images', 'comments.artist', 'artist', 'user'])->find($id);

        // Check if the request exists
        if (!$request) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        // Ensure user data is included in the response
        $request->load('user'); // Optional, but ensures user is always included.

        // Return the request along with user details
        return response()->json($request);
    } catch (\Exception $e) {
        Log::error('Failed to fetch request details: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch request details.'], 500);
    }
}



    // Add a comment on a custom request (allow comments on all requests regardless of status)
    public function addComment(Request $request, $id): JsonResponse
    {
        try {
            // Ensure the artist is authenticated
            $user = $request->user();

            // Find the custom request by ID
            $customRequest = CustomRequest::findOrFail($id);

            // Validate the comment data
            $validatedData = $request->validate([
                'comment' => 'required|string|max:1000',
            ]);

            // Create the comment for the custom request
            $comment = CustomRequestComment::create([
                'custom_request_id' => $customRequest->id,
                'artist_id' => $user->id,
                'comment' => $validatedData['comment'],
            ]);

            // Load the artist relationship to return the artist data with the comment
            $comment->load('artist');

            return response()->json([
                'message' => 'Comment added successfully.',
                'data' => $comment
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $mnfe) {
            Log::warning('Custom request not found for commenting: ID ' . $id);
            return response()->json(['error' => 'Custom request not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning('Validation failed for adding comment: ' . $ve->getMessage());
            return response()->json(['errors' => $ve->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to add comment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add comment.'], 500);
        }
    }
    
    // Delete a comment (allow users to delete their own comments)
    public function deleteComment(Request $request, $commentId): JsonResponse
    {
        try {
            // Ensure the artist is authenticated
            $user = $request->user();

            // Find the comment by ID
            $comment = CustomRequestComment::findOrFail($commentId);

            // Check if the logged-in user is the artist who created the comment
            if ($comment->artist_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized to delete this comment.'], 403);
            }

            // Delete the comment
            $comment->delete();

            return response()->json(['message' => 'Comment deleted successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $mnfe) {
            Log::warning('Comment not found for deletion: ID ' . $commentId);
            return response()->json(['error' => 'Comment not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete comment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete comment.'], 500);
        }
    }

    /**
     * Display a listing of the custom requests.
     * For general users: their own requests.
     * For artists: all pending requests.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->role === 'artist') {
                // Artists see all pending requests with images, user info, and comments
                $customRequests = CustomRequest::with(['images', 'user', 'comments.artist'])
                    ->where('status', 'Pending')
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                // Regular users see their own requests with images and comments
                $customRequests = CustomRequest::with(['images', 'comments.artist'])
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            return response()->json(['data' => $customRequests], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch custom requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch custom requests.'], 500);
        }
    }

    // Store a new custom request
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
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate each image
        ]);
    
        // Create the CustomRequest
        $customRequest = CustomRequest::create([
            'user_id' => $request->user()->id,
            'description' => $validatedData['description'],
            'materials' => $validatedData['materials'] ?? null,
            'dimensions' => $validatedData['dimensions'] ?? null,
            'style_preferences' => $validatedData['style_preferences'] ?? null,
            'budget' => $validatedData['budget'] ?? null,
            'deadline' => $validatedData['deadline'] ?? null,
            'artist_expertise' => $validatedData['artist_expertise'] ?? null,
            'status' => 'Pending',
        ]);
    
        // Handle image uploads and save them to database
        if ($request->hasFile('images') && count($request->file('images')) <= 5) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('custom_request_images', 'public');
                $url = Storage::url($path);
    
                CustomRequestImage::create([
                    'custom_request_id' => $customRequest->id,
                    'url' => $url,
                ]);
            }
        } else {
            return response()->json(['error' => 'You can upload a maximum of 5 images.'], 400);
        }
    
        // Load relationships
        $customRequest->load('images');
    
        return response()->json([
            'message' => 'Custom request created successfully!',
            'data' => $customRequest
        ], 201);
    } catch (\Illuminate\Validation\ValidationException $ve) {
        Log::warning('Validation failed for creating custom request: ' . $ve->getMessage());
        return response()->json(['errors' => $ve->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Failed to create custom request: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create custom request.'], 500);
    }
}
public function getAcceptedRequests(Request $request)
{
    try {
        $user = $request->user();

        // Fetch all accepted requests where the artist is the logged-in user
        $customRequests = CustomRequest::with(['user', 'comments.artist'])
            ->where('artist_id', $user->id)
            ->where('status', 'Accepted')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $customRequests], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to fetch accepted requests.'], 500);
    }
}


    
}
