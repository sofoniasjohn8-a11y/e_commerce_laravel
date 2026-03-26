<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;

class Order extends Model
{
    public function items(){
       return  $this->hasMany(OrderItem::class);
    }
  protected function  casts(): array {
        return [
            'created_at' => 'datetime:d M,Y',
        ];
    }
}
