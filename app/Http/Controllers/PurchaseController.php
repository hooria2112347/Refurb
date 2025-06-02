<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function purchaseHistory(Request $request)
    {
        try {
            $userId = auth()->id();
            
            $purchaseHistory = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.user_id', $userId)
                ->where('orders.status', '!=', 'cancelled')
                ->select(
                    'products.id',
                    'products.name',
                    'products.category',
                    'products.user_id as seller_id',
                    'order_items.quantity',
                    'order_items.price',
                    'orders.created_at as purchase_date',
                    'orders.status'
                )
                ->orderByDesc('orders.created_at')
                ->get();
            
            return response()->json($purchaseHistory);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch purchase history'], 500);
        }
    }
}