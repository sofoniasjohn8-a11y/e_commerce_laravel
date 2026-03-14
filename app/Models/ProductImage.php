<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
       protected $appends = ['image_url'];
       protected $table = 'products_images';
       protected $fillable = ['product_id', 'image'];

     public function getImageUrlAttribute()
{
    if (!$this->image) {
        return null;
        // return asset('images/placeholder.png');
    }

    // Check if the image string already contains 'products/large/'
    if (str_contains($this->image, 'products/small/')) {
        return asset('storage/' . $this->image);
    }

    // Otherwise, add the path (for older records that only have the filename)
    return asset('storage/products/small/' . $this->image);
} 
    
}
