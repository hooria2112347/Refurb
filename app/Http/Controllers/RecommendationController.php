<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecommendationController extends Controller
{
    public function getRecommendations(Request $request)
    {
        try {
            $userId = auth()->id();
            
            // Get user's purchase history
            $purchaseHistory = $this->getUserPurchaseHistory($userId);
            
            // Debug: Log the purchase history to see what we're getting
            Log::info('Purchase History for user ' . $userId, $purchaseHistory);
            
            if (empty($purchaseHistory)) {
                return response()->json([
                    'message' => 'No purchase history found. Showing popular products.',
                    'recommendations' => $this->getPopularProducts()
                ]);
            }
            
            // Generate recommendations based on purchase history
            $recommendations = $this->generateRecommendations($userId, $purchaseHistory);
            
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
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.user_id', $userId)
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'products.id as product_id',
                'products.name',
                'products.category',
                'products.user_id as seller_id',
                'order_items.quantity',
                'orders.created_at as purchase_date'
            )
            ->orderByDesc('orders.created_at') // Get most recent purchases first
            ->get()
            ->toArray();
    }
    
    private function generateRecommendations($userId, $purchaseHistory)
    {
        $recommendations = collect();
        $usedProductIds = collect($purchaseHistory)->pluck('product_id')->toArray();
        
        // Debug: Log what we're working with
        Log::info('Used product IDs: ', $usedProductIds);
        $categories = collect($purchaseHistory)->pluck('category')->unique()->filter()->toArray();
        Log::info('Categories from purchase history: ', $categories);
        
        // Prioritize based on recent purchases and frequency
        
        // 1. Products from same categories (50% weight) - INCREASED PRIORITY
        $sameCategories = $this->getProductsFromSameCategories($purchaseHistory, $usedProductIds, 5);
        $recommendations = $recommendations->merge($sameCategories);
        
        // 2. Products from same sellers (30% weight)
        $sameSellers = $this->getProductsFromSameSellers($purchaseHistory, $usedProductIds, 3);
        $recommendations = $recommendations->merge($sameSellers);
        
        // 3. Popular products only if we don't have enough recommendations (20% weight)
        if ($recommendations->count() < 6) {
            $popular = $this->getPopularProducts($usedProductIds, 6 - $recommendations->count());
            $recommendations = $recommendations->merge($popular);
        }
        
        // Remove duplicates and limit to 8 recommendations
        $finalRecommendations = $recommendations->unique('id')->take(8)->values();
        
        // If we still don't have enough, fill with popular products
        if ($finalRecommendations->count() < 6) {
            $additionalPopular = $this->getPopularProducts(
                $finalRecommendations->pluck('id')->merge($usedProductIds)->toArray(), 
                8 - $finalRecommendations->count()
            );
            $finalRecommendations = $finalRecommendations->merge($additionalPopular)->take(8)->values();
        }
        
        return $finalRecommendations;
    }
    
    private function getProductsFromSameSellers($purchaseHistory, $excludeIds, $limit)
    {
        $sellerIds = collect($purchaseHistory)->pluck('seller_id')->unique()->filter()->toArray();
        
        if (empty($sellerIds)) {
            return collect();
        }
        
        Log::info('Looking for products from sellers: ', $sellerIds);
        
        return Product::with('images') // Load images from product_images table
            ->whereIn('user_id', $sellerIds)
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                $product->recommendation_reason = 'Favorite Seller';
                return $product;
            });
    }
    
    private function getProductsFromSameCategories($purchaseHistory, $excludeIds, $limit)
    {
        // Get categories from purchase history, prioritizing recent ones
        $categoryFrequency = collect($purchaseHistory)
            ->groupBy('category')
            ->map(function ($items) {
                return $items->count();
            })
            ->sortDesc();
            
        $categories = $categoryFrequency->keys()->filter()->toArray();
        
        Log::info('Categories with frequency: ', $categoryFrequency->toArray());
        
        if (empty($categories)) {
            Log::info('No categories found in purchase history');
            return collect();
        }
        
        // Get products from these categories, prioritizing the most frequently purchased categories
        $categoryProducts = collect();
        
        foreach ($categories as $category) {
            $products = Product::with('images') // Load images from product_images table
                ->where('category', $category)
                ->whereNotIn('id', $excludeIds)
                ->inRandomOrder()
                ->limit(ceil($limit / count($categories)) + 1) // Distribute evenly across categories
                ->get();
                
            Log::info("Found {$products->count()} products in category: {$category}");
            
            $categoryProducts = $categoryProducts->merge($products);
        }
        
        return $categoryProducts
            ->take($limit)
            ->map(function ($product) {
                $product->recommendation_reason = 'Based on Your Interests';
                return $product;
            });
    }
    
    private function getPopularProducts($excludeIds = [], $limit = 8)
    {
        $products = Product::with('images') // Load images from product_images table
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->select('products.*', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'))
            ->whereNotIn('products.id', $excludeIds)
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->orderByDesc('products.created_at')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                $product->recommendation_reason = 'Popular';
                return $product;
            });
            
        Log::info("Found {$products->count()} popular products");
        return $products;
    }
    
    // Add this method to help debug
    public function debugRecommendations(Request $request)
    {
        $userId = auth()->id();
        
        $purchaseHistory = $this->getUserPurchaseHistory($userId);
        $categories = collect($purchaseHistory)->pluck('category')->unique()->filter()->toArray();
        
        // Get some products from iron category to verify
        $ironProducts = Product::with('images')->where('category', 'iron')->get(); // Load images from product_images table
        
        return response()->json([
            'user_id' => $userId,
            'purchase_history' => $purchaseHistory,
            'extracted_categories' => $categories,
            'iron_products_count' => $ironProducts->count(),
            'iron_products' => $ironProducts->take(3) // Show first 3 for verification
        ]);
    }
}