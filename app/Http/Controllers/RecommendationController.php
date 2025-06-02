<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecommendationController extends Controller
{
    public function getRecommendations(Request $request)
    {
        try {
            $userId = auth()->id();
            
            // Get user's purchase history and wishlist
            $purchaseHistory = $this->getUserPurchaseHistory($userId);
            $wishlistItems = $this->getUserWishlistItems($userId);
            
            // Debug logging
            Log::info('Purchase History for user ' . $userId, $purchaseHistory);
            Log::info('Wishlist items for user ' . $userId, $wishlistItems);
            
            if (empty($purchaseHistory) && empty($wishlistItems)) {
                return response()->json([
                    'message' => 'No purchase history or wishlist found. Showing popular products.',
                    'recommendations' => $this->getPopularProducts()
                ]);
            }
            
            // Generate recommendations based on purchase history and wishlist
            $recommendations = $this->generateRecommendations($userId, $purchaseHistory, $wishlistItems);
            
            // Debug: Log the recommendations
            Log::info('Generated recommendations for user ' . $userId, $recommendations->toArray());
            
            return response()->json($recommendations);
            
        } catch (\Exception $e) {
            Log::error('Recommendation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Error generating recommendations. Showing popular products.',
                'recommendations' => $this->getPopularProducts()
            ]);
        }
    }
    
    private function getUserPurchaseHistory($userId)
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->where('orders.user_id', $userId)
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'products.product_id',
                'products.name',
                'categories.name as category',
                'products.user_id as seller_id',
                'order_items.quantity',
                'orders.created_at as purchase_date'
            )
            ->orderByDesc('orders.created_at')
            ->get()
            ->toArray();
    }
    
    private function getUserWishlistItems($userId)
    {
        return Wishlist::join('products', 'wishlists.product_id', '=', 'products.product_id')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->where('wishlists.user_id', $userId)
            ->select(
                'products.product_id',
                'products.name',
                'categories.name as category',
                'products.user_id as seller_id',
                'wishlists.created_at as added_date'
            )
            ->orderByDesc('wishlists.created_at')
            ->get()
            ->toArray();
    }
    
    private function generateRecommendations($userId, $purchaseHistory, $wishlistItems)
    {
        $recommendations = collect();
        $usedProductIds = collect($purchaseHistory)->pluck('product_id')
            ->merge(collect($wishlistItems)->pluck('product_id'))
            ->toArray();
        
        // Debug logging
        Log::info('Used product IDs: ', $usedProductIds);
        
        // Get categories from both purchase history and wishlist
        $purchaseCategories = collect($purchaseHistory)->pluck('category')->unique()->filter()->toArray();
        $wishlistCategories = collect($wishlistItems)->pluck('category')->unique()->filter()->toArray();
        
        Log::info('Purchase categories: ', $purchaseCategories);
        Log::info('Wishlist categories: ', $wishlistCategories);
        
        // 1. Products from categories in purchase history (40% weight)
        if (!empty($purchaseCategories)) {
            $purchaseBasedRecommendations = $this->getProductsFromCategories(
                $purchaseCategories, 
                $usedProductIds, 
                4, 
                'Based on Your Order History'
            );
            $recommendations = $recommendations->merge($purchaseBasedRecommendations);
        }
        
        // 2. Products from categories in wishlist (30% weight)
        if (!empty($wishlistCategories)) {
            $wishlistBasedRecommendations = $this->getProductsFromCategories(
                $wishlistCategories, 
                $usedProductIds, 
                3, 
                'Based on Your Interests'
            );
            $recommendations = $recommendations->merge($wishlistBasedRecommendations);
        }
        
        // 3. Products from same sellers (20% weight)
        $sameSellers = $this->getProductsFromSameSellers($purchaseHistory, $usedProductIds, 2);
        $recommendations = $recommendations->merge($sameSellers);
        
        // 4. Fill remaining slots with popular products (10% weight)
        if ($recommendations->count() < 8) {
            $popular = $this->getPopularProducts($usedProductIds, 8 - $recommendations->count());
            $recommendations = $recommendations->merge($popular);
        }
        
        // Remove duplicates and limit to 8 recommendations
        $finalRecommendations = $recommendations->unique('product_id')->take(8)->values();
        
        return $finalRecommendations;
    }
    
    private function getProductsFromCategories($categories, $excludeIds, $limit, $reason)
    {
        if (empty($categories)) {
            return collect();
        }
        
        // Get category IDs from category names
        $categoryIds = DB::table('categories')
            ->whereIn('name', $categories)
            ->pluck('category_id')
            ->toArray();
            
        Log::info("Looking for products in categories: " . implode(', ', $categories));
        Log::info("Category IDs: " . implode(', ', $categoryIds));
        
        return Product::with(['images', 'category'])
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('product_id', $excludeIds)
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(function ($product) use ($reason) {
                return $this->formatProductForRecommendation($product, $reason);
            });
    }
    
    private function getProductsFromSameSellers($purchaseHistory, $excludeIds, $limit)
    {
        $sellerIds = collect($purchaseHistory)->pluck('seller_id')->unique()->filter()->toArray();
        
        if (empty($sellerIds)) {
            return collect();
        }
        
        Log::info('Looking for products from sellers: ', $sellerIds);
        
        return Product::with(['images', 'category'])
            ->whereIn('user_id', $sellerIds)
            ->whereNotIn('product_id', $excludeIds)
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return $this->formatProductForRecommendation($product, 'From Your Favorite Sellers');
            });
    }
    
    private function getPopularProducts($excludeIds = [], $limit = 8)
    {
        // FIXED: Use a subquery to avoid GROUP BY issues
        $popularProductIds = DB::table('products')
            ->leftJoin('order_items', 'products.product_id', '=', 'order_items.product_id')
            ->select('products.product_id', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'))
            ->whereNotIn('products.product_id', $excludeIds)
            ->groupBy('products.product_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->pluck('products.product_id');
            
        // Now get the full product data
        $products = Product::with(['images', 'category'])
            ->whereIn('product_id', $popularProductIds)
            ->get()
            ->map(function ($product) {
                return $this->formatProductForRecommendation($product, 'Popular Products');
            });
            
        Log::info("Found {$products->count()} popular products");
        return $products;
    }
    
    /**
     * Format product data for recommendation response with proper image URLs
     */
    private function formatProductForRecommendation($product, $reason)
    {
        return [
            'product_id' => $product->product_id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'category' => $product->category ? $product->category->name : null,
            'images' => $product->images->map(function ($image) {
                return url('images/' . $image->image_path);
            }),
            'recommendation_reason' => $reason,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];
    }
    
    // Debug method to help troubleshoot
    public function debugRecommendations(Request $request)
    {
        $userId = auth()->id();
        
        $purchaseHistory = $this->getUserPurchaseHistory($userId);
        $wishlistItems = $this->getUserWishlistItems($userId);
        
        $purchaseCategories = collect($purchaseHistory)->pluck('category')->unique()->filter()->toArray();
        $wishlistCategories = collect($wishlistItems)->pluck('category')->unique()->filter()->toArray();
        
        return response()->json([
            'user_id' => $userId,
            'purchase_history' => $purchaseHistory,
            'wishlist_items' => $wishlistItems,
            'purchase_categories' => $purchaseCategories,
            'wishlist_categories' => $wishlistCategories,
        ]);
    }
}