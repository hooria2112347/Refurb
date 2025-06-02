<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ProductController extends Controller
{ 
    public function store(Request $request)
    {
        // 1. Check if user is logged in
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'category_id' => 'required|exists:categories,category_id',
                'additional_info' => 'nullable|string',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:20480', // 20 MB
            ]);

            // Add the user_id to the validated array
            $validated['user_id'] = auth()->id();

            // Create the product
            $product = Product::create($validated);

            // Handle image uploads
            if ($request->hasFile('images')) {
                // Make sure the images directory exists
                if (!file_exists(public_path('images'))) {
                    mkdir(public_path('images'), 0755, true);
                }
                
                foreach ($request->file('images') as $image) {
                    // Save directly to public/images folder with unique filename
                    $filename = uniqid() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('images'), $filename);
                    
                    // Log the path for debugging
                    Log::info('Image stored:', ['path' => $filename, 'full_path' => public_path('images/' . $filename)]);

                    ProductImage::create([
                        'product_id' => $product->product_id,
                        'image_path' => $filename, // Store just the filename
                    ]);
                }
            }

            return response()->json(['message' => 'Product added successfully!'], 201);
        } catch (\Exception $e) {
            Log::error('Error adding product: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function index()
    {
        // 2. Check if user is logged in
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            Log::info('Fetching products for authenticated user...');

            // Only fetch the products where user_id = the ID of the logged-in user
            $products = Product::where('user_id', auth()->id())
                ->with('images')
                ->get();

            $response = $products->map(function ($product) {
                // Get the first image if available
                $image = null;
                if ($product->images->isNotEmpty()) {
                    // Use direct URL to the images folder
                    $filename = $product->images->first()->image_path;
                    $image = url('images/' . $filename);
                    
                    // Log image path for debugging
                    Log::info('Image path for product:', [
                        'product_id' => $product->product_id,
                        'filename' => $filename,
                        'full_url' => $image,
                        'file_exists' => file_exists(public_path('images/' . $filename))
                    ]);
                }

                return [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'image' => $image,
                ];
            });

            Log::info('Fetched products successfully:', ['products' => $response]);

            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching products:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching products', 'error' => $e->getMessage()], 500);
        }
    }

public function update(Request $request, $id)
{
    // Check if user is logged in
    if (!auth()->check()) {
        return response()->json(['error' => 'Unauthorized (please log in).'], 401);
    }

    try {
        // Fetch product by product_id AND user_id
        $product = Product::where('product_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found or unauthorized.'], 404);
        }

        // Updated validation to include all fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,category_id', // Add this line
            'additional_info' => 'nullable|string' // Add this line
        ]);

        // Update the product with all validated fields
        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'category_id' => $validated['category_id'],
            'additional_info' => $validated['additional_info'] ?? null
        ]);

        // Return the updated product data
        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product->fresh() // Get fresh data from database
        ], 200);

    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error updating product:', ['error' => $e->getMessage()]);
        return response()->json([
            'message' => 'Error updating product', 
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function show($id)
    {
        try {
            // Fetch the product with related data
            $product = Product::with(['images', 'category', 'user', 'comments.user'])
                ->find($id);

            // Check if the product exists
            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            // Prepare the response data
            $response = [
                'id' => $product->product_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'images' => $product->images->map(function($image) {
                    $url = url('images/' . $image->image_path);
                    
                    // Log image path for debugging
                    Log::info('Image path for product detail:', [
                        'filename' => $image->image_path,
                        'full_url' => $url,
                        'file_exists' => file_exists(public_path('images/' . $image->image_path))
                    ]);
                    
                    return $url;
                }),
                'user' => [
                    'id' => $product->user->id,
                    'name' => $product->user->name,
                ],
                'comments' => $product->comments->map(function($comment) {
                    return [
                        'id' => $comment->id,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                        'comment' => $comment->comment,
                        'rating' => $comment->rating,
                        'created_at' => $comment->created_at->toDateTimeString(),
                    ];
                }),
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching product details:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching product details', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAllProducts(Request $request)
    {
        try {
            // Fetch all products, eager load category and images
            $products = Product::with(['category', 'images'])->get();
    
            // Map the products into the desired response format
            $response = $products->map(function ($product) {
                return [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'category' => $product->category ? $product->category->name : null,
                    'images' => $product->images->map(function ($image) {
                        $url = url('images/' . $image->image_path);
                        
                        // Log image path for debugging
                        Log::info('getAllProducts image path:', [
                            'filename' => $image->image_path,
                            'full_url' => $url,
                            'file_exists' => file_exists(public_path('images/' . $image->image_path))
                        ]);
                        
                        return $url;
                    }),
                ];
            });
    
            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('Error in getAllProducts:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error fetching products', 'error' => $e->getMessage()], 500);
        }
    }
// Add this new method to your ProductController
public function getUserProducts($userId)
{
    try {
        // Fetch products for specific user
        $products = Product::where('user_id', $userId)
            ->with(['category', 'images'])
            ->get();

        $response = $products->map(function ($product) {
            return [
                'id' => $product->product_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category ? $product->category->name : null,
                'images' => $product->images->map(function ($image) {
                    return url('images/' . $image->image_path);
                }),
            ];
        });

        return response()->json($response, 200);
    } catch (\Exception $e) {
        Log::error('Error fetching user products:', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Error fetching user products', 'error' => $e->getMessage()], 500);
    }
}


    public function destroy($id)
    {
        // 4. Check if user is logged in
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            Log::info('Received request to delete product with product_id:', ['product_id' => $id]);

            $product = Product::where('product_id', $id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$product) {
                Log::warning('Product not found or unauthorized for product_id:', ['product_id' => $id]);
                return response()->json(['message' => 'Product not found or unauthorized.'], 404);
            }

            // Delete associated images from the images folder
            foreach ($product->images as $img) {
                $imagePath = public_path('images/' . $img->image_path);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $img->delete();
            }

            $product->delete();
            Log::info('Product deleted successfully:', ['product_id' => $id]);

            return response()->json(['message' => 'Product deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting product:', ['product_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting product', 'error' => $e->getMessage()], 500);
        }
    }
}