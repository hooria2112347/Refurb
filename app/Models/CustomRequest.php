<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomRequest extends Model
{
    
    use HasFactory;

    protected $table = 'custom_requests';
    protected $fillable = [
        'user_id',             // The user creating the request
        'description',         // Description of the request
        'materials',           // Preferred materials
        'dimensions',          // Dimensions of the product
        'style_preferences',   // Style preferences
        'budget',              // Budget
        'deadline',            // Deadline
        'artist_expertise',    // Artist expertise
        'images',              // JSON-encoded image paths
        'status',              // Default status (e.g., 'Pending')
    ];
}