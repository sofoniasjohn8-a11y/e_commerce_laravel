<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class productController extends Controller
{
    public function getProducts(Request $request){
        $products  = Product::orderBy('created_at','DESC')
        ->where('status',1);
      
       if(!empty($request->category)){
        $catArray = explode(',',$request->category);
        $products = $products->whereIn('category_id',$catArray);
       }
       if(!empty($request->brand)){
        $brandArray = explode(',',$request->brand);
        $products = $products->whereIn('brand_id',$brandArray);
       }
       
        $products = $products->get();
       
        return response()->json([
            'status' => 200,
            'data'   => $products
        ]);
    }
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
    public function getCategories(){
        $categories = Category::orderBy('name','ASC')
        ->where('status',1)
        ->get();

        return response()->json([
            'status' => 200,
            'data'   => $categories
        ]);

    }
      public function getBrands(){
        $brands = Brand::orderBy('name','ASC')
        ->where('status',1)
        ->get();

        return response()->json([
            'status' => 200,
            'data'   => $brands
        ]);

    }
    public function getProduct($id){
        $product = Product::with('product_images','product_sizes.size')->find($id);
        return response()->json([
            'status' => 200,
            'data'   => $product
        ]);
    }
}
