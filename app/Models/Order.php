<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';
    
    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'shipping_address',
        'contact_phone',
        'payment_method',
        'notes'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }
    
    // Helper method to get all products in this order
    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            OrderItem::class,
            'order_id', // Foreign key on OrderItem table
            'product_id', // Foreign key on Product table
            'order_id', // Local key on Order table
            'product_id' // Local key on OrderItem table
        );
    }
}