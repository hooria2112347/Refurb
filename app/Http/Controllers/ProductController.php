<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
            $validated['user_id'] = auth()->id();  // Or Auth::id()

            // Create the product
            $product = Product::create($validated);

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product_images', 'public');

                    ProductImage::create([
                        'product_id' => $product->product_id,
                        'image_path' => $path,
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
                return [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'image' => $product->images->first()
                        ? asset('storage/' . $product->images->first()->image_path)
                        : null,
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
        // 3. Check if user is logged in
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

            // Optional: delete associated images from storage
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img->image_path);
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
