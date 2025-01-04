<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'category_id' => 'required|exists:categories,category_id',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:20480', // 20 MB
            ]);
    
            // Save the product
            $product = new Product($validated);
            $product->user_id = $request->user()->id;
            $product->save();
    
            // Handle image upload
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                foreach ($images as $image) {
                    $path = $image->store('products', 'public'); // Save to public disk
                    ProductImage::create([
                        'product_id' => $product->product_id,
                        'image_path' => $path,
                    ]);
                }
            }
    
            return response()->json(['message' => 'Product added successfully!', 'product' => $product], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    } // app/Http/Controllers/ProductController.php

    // public function show($id)
    // {
    //     try {
    //         // Find the product by its ID
    //         $product = Product::with(['category', 'images'])->findOrFail($id);
    
    //         // Format the response
    //         return response()->json([
    //             'id' => $product->product_id,
    //             'name' => $product->name,
    //             'description' => $product->description,
    //             'price' => $product->price,
    //             'category' => $product->category ? $product->category->name : null,
    //             'image' => $product->images->first()
    //                 ? asset('storage/' . $product->images->first()->image_path)
    //                 : null,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Product not found.'], 404);
    //     }
    // }
    
    public function index(Request $request)
    {
        try {
            // Fetch only products for the logged-in user
            $products = Product::where('user_id', $request->user()->id)
                ->with(['category', 'images']) // Eager load the category and images relationships
                ->get();
    
            // Format the products for the response
            $response = $products->map(function ($product) {
                return [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'category' => $product->category ? $product->category->name : null,
                    'image' => $product->images->first()
                        ? asset('storage/' . $product->images->first()->image_path)
                        : null,
                ];
            });
    
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching products', 'error' => $e->getMessage()], 500);
        }
    }public function getAllProducts(Request $request)
    {
        try {
            // Fetch all products with their associated category, images, and user (scrap seller)
            $products = Product::with(['category', 'images', 'user'])->get();
    
            // Map the products into the desired response format
            $response = $products->map(function ($product) {
                return [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'category' => $product->category ? $product->category->name : null,
                    'images' => $product->images->map(function ($image) {
                        return asset('storage/' . $image->image_path); // Full image URL
                    }),
                    'user' => $product->user->name, // Get the name of the user who uploaded the product
                ];
            });
    
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching products', 'error' => $e->getMessage()], 500);
        }
    }
    

    public function show($id): JsonResponse
    {
        try {
            // Fetch the product with associated user, category, images, and artist (if applicable)
            $product = Product::with(['category', 'images', 'user'])->find($id);
            
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }
            
            // Prepare product data to send back to the frontend
            $productData = [
                'id' => $product->product_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category' => $product->category ? $product->category->name : null,
                'images' => $product->images->map(function ($image) {
                    return asset('storage/' . $image->image_path); // Full image URL
                }),
                'user' => [
                    'id' => $product->user->id,  // User ID
                    'name' => $product->user->name,  // User name (scrap seller)
                ]
            ];
    
            // Log the product data for debugging purposes
            \Log::info('Product details fetched:', ['product' => $productData]);
    
            return response()->json($productData, 200);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching product details: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch product details.'], 500);
        }
    }
    
        
    

    public function update(Request $request, $id)
    {
        try {
            $product = Product::where('user_id', $request->user()->id)->where('product_id', $id)->first();

            if (!$product) {
                return response()->json(['message' => 'Product not found or unauthorized access.'], 404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
            ]);

            $product->update($validated);

            return response()->json(['message' => 'Product updated successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error updating product:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error updating product', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            Log::info('Received request to delete product with product_id:', ['product_id' => $id]);

            $product = Product::where('user_id', $request->user()->id)->where('product_id', $id)->first();

            if (!$product) {
                Log::warning('Product not found or unauthorized access for product_id:', ['product_id' => $id]);
                return response()->json(['message' => 'Product not found or unauthorized access.'], 404);
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
