<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{ 
    public function updateOrderItemStatus(Request $request, $orderId)
{
    if (!auth()->check()) {
        return response()->json(['error' => 'Unauthorized (please log in).'], 401);
    }

    // Validate the request
    $validator = Validator::make($request->all(), [
        'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
        'item_id' => 'nullable|integer' // Optional - if not provided, update all items
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        // Start transaction
        DB::beginTransaction();
        
        // If item_id is provided, update specific item
        if ($request->has('item_id')) {
            $item = OrderItem::where('id', $request->item_id)
                ->whereHas('product', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();
                
            if (!$item) {
                return response()->json(['error' => 'Order item not found or you do not have permission'], 404);
            }
            
            $item->status = $request->status;
            $item->save();
            
            // Check if all items in the order have the same status
            $allItemsSameStatus = OrderItem::where('order_id', $orderId)
                ->whereHas('product', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->where('status', '!=', $request->status)
                ->doesntExist();
                
            // If all items have the same status, update the order status too
            if ($allItemsSameStatus) {
                $order = Order::findOrFail($orderId);
                $order->status = $request->status;
                $order->save();
            }
        } 
        // If no item_id, update all items belonging to this seller
        else {
            $items = OrderItem::where('order_id', $orderId)
                ->whereHas('product', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->get();
                
            if ($items->isEmpty()) {
                return response()->json(['error' => 'No order items found for this seller'], 404);
            }
            
            foreach ($items as $item) {
                $item->status = $request->status;
                $item->save();
            }
            
            // Check if these are all the items in the order or if there are other items from other sellers
            $allItemsInOrder = OrderItem::where('order_id', $orderId)->count();
            $sellerItemsCount = $items->count();
            
            // If all items in the order belong to this seller, update the order status too
            if ($allItemsInOrder === $sellerItemsCount) {
                $order = Order::findOrFail($orderId);
                $order->status = $request->status;
                $order->save();
            }
        }
        
        // Commit transaction
        DB::commit();
        
        return response()->json([
            'message' => 'Order status updated successfully',
            'status' => $request->status
        ], 200);
        
    } catch (\Exception $e) {
        // Rollback transaction on error
        DB::rollBack();
        Log::error('Error updating order status: ' . $e->getMessage());
        return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
    }
}
    // Create a new order from the user's cart
    public function checkout(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        // Validate checkout data
        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|string',
            'contact_phone' => 'required|string',
            'payment_method' => 'required|string|in:cash_on_delivery,credit_card,bank_transfer',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Start transaction
            DB::beginTransaction();
            
            // Get user's cart items
            $cartItems = Cart::where('user_id', auth()->id())
                ->with('product')
                ->get();
                
            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Your cart is empty'], 400);
            }
            
            // Calculate total amount
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item->product->price * $item->quantity;
            }
            
            // Create new order
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'contact_phone' => $request->contact_phone,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes
            ]);
            
            // Create order items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price
                ]);
            }
            
            // Clear user's cart
            Cart::where('user_id', auth()->id())->delete();
            
            // Commit transaction
            DB::commit();
            
            return response()->json([
                'message' => 'Order placed successfully',
                'order_id' => $order->order_id,
                'status' => $order->status
            ], 201);
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    
    // Get user's orders
    
public function getSellerOrders()
{
    $sellerId = auth()->id();
    
    if (!$sellerId) {
        return response()->json(['error' => 'Unauthorized (please log in).'], 401);
    }
    
    try {
        $orders = Order::whereHas('orderItems.product', function($query) use ($sellerId) {
                $query->where('user_id', $sellerId);
            })
            ->with(['orderItems' => function($query) use ($sellerId) {
                $query->whereHas('product', function($q) use ($sellerId) {
                    $q->where('user_id', $sellerId);
                })->with('product.images');
            }])
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Filter only the seller's items and transform data
                $sellerItems = $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->product->name,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'status' => $item->status ?? 'pending', // Make sure status exists
                        'image_path' => $item->product->images->first() 
                            ? asset('storage/' . $item->product->images->first()->image_path) 
                            : null
                    ];
                });
                
                // Calculate total amount for seller's items only
                $totalAmount = $sellerItems->sum(function($item) {
                    return $item['price'] * $item['quantity'];
                });
                
                return [
                    'order_id' => $order->order_id,
                    'customer_name' => $order->user->name,
                    'total_amount' => $totalAmount,
                    'status' => $order->status,
                    'shipping_address' => $order->shipping_address,
                    'contact_phone' => $order->contact_phone,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at,
                    'items' => $sellerItems
                ];
            });
        
        return response()->json($orders);
    } catch (\Exception $e) {
        Log::error('Error fetching seller orders: ' . $e->getMessage());
        return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
    }
}
public function getUserOrders()
{
    if (!auth()->check()) {
        return response()->json(['error' => 'Unauthorized (please log in).'], 401);
    }

    try {
        $orders = Order::where('user_id', auth()->id())
            ->with(['orderItems.product.images'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'order_id' => $order->order_id,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'shipping_address' => $order->shipping_address,
                    'contact_phone' => $order->contact_phone,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at,
                    'items' => $order->orderItems->map(function ($item) {
                        // Fix: Correctly format image path
                        $imagePath = null;
                        if ($item->product->images->first()) {
                            $imagePath = asset('storage/' . $item->product->images->first()->image_path);
                        }
                        
                        return [
                            'id' => $item->id, // Add id field which is used in v-for key
                            'product_id' => $item->product_id,
                            'name' => $item->product->name,
                            'price' => $item->price,
                            'quantity' => $item->quantity,
                            'image_path' => $imagePath // Match the property name used in Vue template
                        ];
                    })
                ];
            });
            
        // Return directly the array of orders (option 1)
        return response()->json($orders, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching user orders: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    
    // Get order details
    public function getOrderDetails($orderId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            $order = Order::where('order_id', $orderId)
                ->where('user_id', auth()->id())
                ->with(['orderItems.product.images'])
                ->first();
                
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            return response()->json([
                'order' => [
                    'order_id' => $order->order_id,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'shipping_address' => $order->shipping_address,
                    'contact_phone' => $order->contact_phone,
                    'payment_method' => $order->payment_method,
                    'notes' => $order->notes,
                    'created_at' => $order->created_at,
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'name' => $item->product->name,
                            'price' => $item->price,
                            'quantity' => $item->quantity,
                            'subtotal' => $item->price * $item->quantity,
                            'image' => $item->product->images->first() ? 
                                asset('storage/' . $item->product->images->first()->image_path) : 
                                null
                        ];
                    })
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching order details: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    
    // Cancel order (only if status is pending)
    public function cancelOrder($orderId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized (please log in).'], 401);
        }

        try {
            $order = Order::where('order_id', $orderId)
                ->where('user_id', auth()->id())
                ->first();
                
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            if ($order->status !== 'pending') {
                return response()->json([
                    'error' => 'Cannot cancel order. Order status is: ' . $order->status
                ], 400);
            }
            
            $order->status = 'cancelled';
            $order->save();
            
            return response()->json([
                'message' => 'Order cancelled successfully',
                'order_id' => $order->order_id,
                'status' => $order->status
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error cancelling order: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}