<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSize extends Model
{
    protected $table = 'products_sizes';
    
    public function size(){
       return $this->belongsTo(Size::class);
   }
}
