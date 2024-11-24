<?php

// app/Http/Controllers/ProductController.php

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
            'category_id' => 'required|exists:categories,category_id',
            'additional_info' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:20480', 
        ]);

        Log::info('Validated data:', $validated);

        $product = Product::create($validated);

        Log::info('Product created:', $product->toArray());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('product_images', 'public');
                ProductImage::create([
                    'product_id' => $product->product_id,
                    'image_path' => $path,
                ]);
                Log::info('Image stored at:', ['path' => $path]);
            }
        }

        return response()->json(['message' => 'Product added successfully!', 'product' => $product], 201);
    } catch (\Exception $e) {
        Log::error('Error adding product:', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Error adding product', 'error' => $e->getMessage()], 500);
    }
}
}