<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Feedback; // Ensure Feedback model is imported
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    public function __construct()
    {
        // Ensure the user is authenticated
        $this->middleware('auth:sanctum');
    }
    
    public function getAllFeedback()
    {
        // Eager-load the related project and user so you can do feedback.project.title, feedback.user.name
        $feedback = Feedback::with(['project', 'user'])->get();

        return response()->json($feedback);
    }
    /**
     * Display all products, optionally filtered by `user_id` (the scrapseller).
     * Example: GET /api/admin/products?user_id=5
     */
    public function index(Request $request)
    {
        try {
            // Build a query to get all products
            $query = Product::with(['images', 'user']); // Eager-load images & user relationship

            // If we have `?user_id=xxx`, filter by that scrapseller
            if ($request->has('user_id') && !empty($request->user_id)) {
                $query->where('user_id', $request->user_id);
            }

            $products = $query->get();

            // Transform data in a more convenient format
            $response = $products->map(function ($product) {
                return [
                    'id'          => $product->product_id, // Assuming 'product_id' is your primary key
                    'name'        => $product->name,
                    'description' => $product->description,
                    'price'       => $product->price,
                    'user_id'     => $product->user_id,  // So admin knows who owns it
                    'user_name'   => optional($product->user)->name,
                    'image'       => $product->images->first()
                        ? asset('storage/' . $product->images->first()->image_path)
                        : null,
                ];
            });

            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching all products: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * Update any product by ID.
     * Example: PUT /api/admin/products/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'required|string',
                'price'       => 'required|numeric',
            ]);

            // Admin can find by product_id only (no user_id filter)
            $product = Product::where('product_id', $id)->first();

            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            $product->update($validated);

            return response()->json(['message' => 'Product updated successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * Delete any product by ID.
     * Example: DELETE /api/admin/products/{id}
     */
    public function destroy($id)
    {
        try {
            // Admin can find by product_id only
            $product = Product::where('product_id', $id)
                ->with('images')
                ->first();

            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            // Delete associated images from storage
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img->image_path);
                $img->delete();
            }

            $product->delete();

            return response()->json(['message' => 'Product deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}
