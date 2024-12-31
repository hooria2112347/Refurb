<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomRequestImage extends Model
{
    use HasFactory;

    protected $table = 'custom_request_images';

    protected $fillable = [
        'custom_request_id',
        'url',
    ];

    /**
     * Get the custom request that owns the image.
     */
    public function customRequest()
    {
        return $this->belongsTo(CustomRequest::class);
    }
}
