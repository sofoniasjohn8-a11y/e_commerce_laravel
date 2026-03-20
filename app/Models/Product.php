<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductImage;
use App\Models\ProductSize;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'price',
        'discount_price',
        'description',
        'short_description',
        'image',
        'category_id',
        'brand_id',
        'qty',
        'sku',
        'barcode',
        'status',
        'is_featured',
        'compare_price',
    ];
   protected $appends = ['image_url'];

   public function getImageUrlAttribute()
{
    if (!$this->image) {
        return null;
        // return asset('images/placeholder.png');
    }

    // Check if the image string already contains 'products/large/'
    if (str_contains($this->image, 'products/large/')) {
        return asset('storage/' . $this->image);
    }

    // Otherwise, add the path (for older records that only have the filename)
    return asset('storage/products/large/' . $this->image);
}
    public function product_images(){
        return $this->hasMany(ProductImage::class,'product_id');
    }
    public function product_sizes(){
        return $this->hasMany(ProductSize::class,'product_id');
    }

}
