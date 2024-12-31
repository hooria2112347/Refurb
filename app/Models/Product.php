<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products'; 
    protected $primaryKey = 'product_id'; // Use your actual primary key


    // Specify the fields that are mass-assignable
    protected $fillable = [
        'name', 'description', 'price', 'category_id', 'user_id', 'additional_info'
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
