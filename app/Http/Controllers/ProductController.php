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
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,category_id', // Use `category_id` here
            'additional_info' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:20480', // 20 MB
        ]);
        

        // Create the product
        $product = Product::create($validated);

        if (!isset($product->product_id)) {
            throw new \Exception('Product ID not generated');
        }

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
    try {
        Log::info('Fetching all products from the database...');
        $products = Product::with('images')->get();

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
            $product = Product::find($id);

            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
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
    try {
        Log::info('Received request to delete product with product_id:', ['product_id' => $id]);

        $product = Product::where('product_id', $id)->first();

        if (!$product) {
            Log::warning('Product not found for product_id:', ['product_id' => $id]);
            return response()->json(['message' => 'Product not found.'], 404);
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