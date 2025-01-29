<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  The ID of the product to review.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $id)
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Validate input
            $validated = $request->validate([
                'comment' => 'required|string',
                'rating' => 'required|integer|min:1|max:5',
            ]);

            // Find the product
            $product = Product::find($id);
            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            // Create the comment
            $comment = Comment::create([
                'product_id' => $product->product_id,
                'user_id' => auth()->id(),
                'comment' => $validated['comment'],
                'rating' => $validated['rating'],
            ]);

            return response()->json(['message' => 'Review submitted successfully.', 'comment' => $comment], 201);
        } catch (\Exception $e) {
            Log::error('Error submitting review:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error submitting review', 'error' => $e->getMessage()], 500);
        }
    }
}
