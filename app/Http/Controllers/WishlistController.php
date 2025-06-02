<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    // Add product to wishlist
    public function addToWishlist($productId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            // Check if product exists using the correct primary key
            $product = Product::where('product_id', $productId)->first();
            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            // Check if product is already in wishlist
            $existingWishlistItem = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->first();

            if ($existingWishlistItem) {
                return response()->json(['message' => 'Product already in wishlist.'], 200);
            }

            // Add to wishlist
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $productId
            ]);

            return response()->json(['message' => 'Product added to wishlist.'], 201);
        } catch (\Exception $e) {
            Log::error('Error adding to wishlist: ' . $e->getMessage());
            return response()->json(['error' => 'Server error', 'details' => $e->getMessage()], 500);
        }
    }

    // Remove product from wishlist
    public function removeFromWishlist($productId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            $deleted = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->delete();

            if ($deleted) {
                return response()->json(['message' => 'Product removed from wishlist.'], 200);
            } else {
                return response()->json(['message' => 'Product not found in wishlist.'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error removing from wishlist: ' . $e->getMessage());
            return response()->json(['error' => 'Server error', 'details' => $e->getMessage()], 500);
        }
    }

    // Get user's wishlist
    public function getWishlist()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            $wishlist = Wishlist::where('user_id', auth()->id())
                ->with(['product.images'])
                ->get();

            $response = $wishlist->map(function ($item) {
                $product = $item->product;
                
                // Check if product exists (in case it was deleted)
                if (!$product) {
                    return null;
                }
                
                return [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'original_price' => null, // Add this if you have original price
                    'price_dropped' => false, // Add logic if you track price drops
                     'images' => $product->images->map(function ($image) {
                            $url = url('images/' . $image->image_path);
                         return $url;    
                    }),
                    'added_at' => $item->created_at
                ];
            })->filter(); // Remove null entries
           
            return response()->json($response->values(), 200);
        } catch (\Exception $e) {
            Log::error('Error fetching wishlist: ' . $e->getMessage());
            return response()->json(['error' => 'Server error', 'details' => $e->getMessage()], 500);
        }
    }
}