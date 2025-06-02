<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'product_id', 
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the product associated with the cart item
     * Since Product model uses 'product_id' as primary key, we need to specify the foreign key
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Get the user who owns the cart item
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}