<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{public function store(Request $request)
    {
        try {
            // Log all request headers for debugging
            Log::info('Request Headers:', $request->headers->all());
    
            // Log the Authorization header
            Log::info('Authorization Header:', ['Authorization' => $request->header('Authorization')]);
    
            // Log the authenticated user
            Log::info('Authenticated User:', ['user' => $request->user()]);
    
            if (!$request->user()) {
                throw new \Exception('User not authenticated');
            }
    
            // Proceed with product creation
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'category_id' => 'required|exists:categories,category_id',
                'additional_info' => 'nullable|string',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:20480', // 20 MB
            ]);
    
            $product = new Product($validated);
            $product->user_id = $request->user()->id;
            $product->save();
    
            return response()->json(['message' => 'Product added successfully!', 'product' => $product], 201);
        } catch (\Exception $e) {
            Log::error('Error adding product: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    public function index(Request $request)
    {
        try {
            Log::info('Fetching products for user:', ['user_id' => $request->user()->id]);
            $products = Product::where('user_id', $request->user()->id)->with('images')->get();

            $response = $products->map(function ($product) {
                return [
                    'id' => $product->product_id, // Use 'product_id' instead of 'id'
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
