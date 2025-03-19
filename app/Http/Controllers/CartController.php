<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    // Add product to cart
    public function addToCart(Request $request, $productId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            // Check if product exists
            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            // Validate quantity
            $request->validate([
                'quantity' => 'integer|min:1|nullable'
            ]);
            
            $quantity = $request->input('quantity', 1);

            // Check if product is already in cart
            $existingCartItem = Cart::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->first();

            if ($existingCartItem) {
                // Update quantity if product already exists in cart
                $existingCartItem->quantity += $quantity;
                $existingCartItem->save();
                $message = 'Cart updated successfully.';
            } else {
                // Add new item to cart
                Cart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
                $message = 'Product added to cart.';
            }

            return response()->json(['message' => $message], 201);
        } catch (\Exception $e) {
            Log::error('Error adding to cart: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // Update cart item quantity
    public function updateCartItem(Request $request, $productId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $cartItem = Cart::where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->first();

            if (!$cartItem) {
                return response()->json(['message' => 'Product not found in cart.'], 404);
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            return response()->json(['message' => 'Cart updated successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error updating cart: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // Remove product from cart
    public function removeFromCart($productId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
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

    // Get user's cart
    public function getCart()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            $cart = Cart::where('user_id', auth()->id())
                ->with(['product.images'])
                ->get();

            $items = $cart->map(function ($item) {
                $product = $item->product;
                return [
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $product->price * $item->quantity,
                    'images' => $product->images->map(function ($image) {
                        return asset('storage/' . $image->image_path);
                    }),
                    'added_at' => $item->created_at
                ];
            });

            $total = $items->sum('subtotal');

            return response()->json([
                'items' => $items,
                'total' => $total
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching cart: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // Clear entire cart
    public function clearCart()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            Cart::where('user_id', auth()->id())->delete();
            return response()->json(['message' => 'Cart cleared successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error clearing cart: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}