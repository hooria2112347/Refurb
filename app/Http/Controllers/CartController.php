<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    // Add product to cart
    public function addToCart(Request $request, $productId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            $quantity = $request->input('quantity', 1);
            if ($quantity < 1) {
                return response()->json(['error' => 'Quantity must be at least 1.'], 422);
            }

            $cartItem = Cart::firstOrCreate(
                ['user_id' => auth()->id(), 'product_id' => $productId],
                ['quantity' => 0]
            );

            $cartItem->quantity += $quantity;
            $cartItem->save();

            return response()->json(['message' => 'Product added to cart.'], 201);
        } catch (\Exception $e) {
            Log::error('Add to cart error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // Get all cart items
    public function getCart()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $cartItems = Cart::with('product')
                ->where('user_id', auth()->id())
                ->get()
                ->filter(function ($item) {
                    return $item->product !== null;
                })
                ->map(function ($item) {
                    $product = $item->product;

                    return [
                        'id' => $product->product_id, // Use product_id since that's the primary key
                        'product_id' => $product->product_id, // The actual primary key
                        'cart_id' => $item->id, // Add cart item ID if needed
                        'name' => $product->name,
                        'description' => $product->description ?? '',
                        'price' => (float) $product->price,
                        'quantity' => (int) $item->quantity,
                        'subtotal' => (float) ($product->price * $item->quantity),
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
                        'added_at' => $item->created_at
                    ];
                })->values();

            return response()->json([
                'items' => $cartItems,
                'total_items' => $cartItems->sum('quantity'),
                'total_amount' => $cartItems->sum('subtotal')
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get cart error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // Update item quantity
    public function updateCartItem(Request $request, $productId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $cartItem = Cart::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->first();

            if (!$cartItem) {
                return response()->json(['message' => 'Item not found in cart.'], 404);
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            return response()->json(['message' => 'Cart updated successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Update cart error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function removeFromCart($productId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        // Add validation to ensure productId is not null
        if (!$productId) {
            return response()->json(['error' => 'Product ID is required.'], 400);
        }

        try {
            $deleted = Cart::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->delete();

            if ($deleted) {
                return response()->json(['message' => 'Product removed from cart.'], 200);
            } else {
                return response()->json(['message' => 'Product not found in cart.'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error removing from cart: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // Clear cart
    public function clearCart()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            Cart::where('user_id', auth()->id())->delete();
            return response()->json(['message' => 'Cart cleared.'], 200);
        } catch (\Exception $e) {
            Log::error('Clear cart error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}