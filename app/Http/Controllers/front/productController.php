<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class productController extends Controller
{
    public function latest(){
        $product  = Product::orderBy('created_at','DESC')
        ->where('status',1)
        ->limit(8)
        ->get();

        return response()->json([
            'status' => 200,
            'data'   => $product
        ]);
    }
    public function featured(){
        $product  = Product::orderBy('created_at','DESC')
        ->where('status',1)
        ->where('is_featured','yes')
        ->limit(8)
        ->get();

        return response()->json([
            'status' => 200,
            'data'   => $product
        ]);
    }
}
