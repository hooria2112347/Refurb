<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    // Specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'product_images';

    // Define the primary key
    protected $primaryKey = 'image_id';

    // If the primary key is not an auto-incrementing integer, set these properties accordingly
    public $incrementing = true; // Set to false if image_id is not auto-incrementing
    protected $keyType = 'int';   // Set to 'string' if image_id is a string

    // Define fillable attributes
    protected $fillable = [
        'product_id',
        'image_path',
    ];

    // Define the relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
