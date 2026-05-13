<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Facades\Validator; 

class BrandController extends Controller
{
       public function index(){
        $brands = Brand::orderBy('created_at','DESC')->get();
        if($brands){
            return response()->json([
            'status' => 200,
            'data'  => $brands
        ]);
        }
        return response()->json([
            'status' => 200,
            'data'  => []
        ]);
    }
    public function store(Request $request){
      $validator = Validator::make($request->all(),[
        'name'=>'required'
      ]);
      
      if($validator->fails()){
        return response()->json([
            'status'=>400,
            'errors'=>$validator->errors()
        ],400);
      }
        $brand1 = new Brand();
        $brand1->name = $request->name;
        $brand1->status = $request->status;
        $brand1->save();

        
        return response()->json([
            'status' => 200,
            'data'  => $brand1,
            'message'=>'brand added succesfully'
        ],200);
    
    }
    public function show($id){
        $brand = Brand::find($id);
        if($brand){
            return response()->json([
                'status'=>200,
                'data'=>$brand
            ]);
        }
        return response()->json([
            'status'=>400,
            'data'=>[]
        ]);
    }
    public function update($id,Request $request){
        $brand = Brand::find($id);
        if(!$brand){
             return response()->json([
                'message'=>'the category is not found'
            ]);
        }
        $brand->name = $request->name?$request->name:$brand->name;
        $brand->status = $request->status ? $request->status  : $brand->status;
            // $category->status = $request->status;
            $brand->save();
         return response()->json([
                'status'=>200,
                'data' => $brand ,
                'message'=>'category updated succesfully'
            ]);
    }
    public function destroy($id){
        $brand = Brand::find($id);
        if(!$brand){
            return response()->json([
                'status'=>400,
                'message'=>'the category is not found'
            ],400);
        }
        $brand->delete();
        return response()->json([
            'status'=>200,
            'message'=>'you have deleted category succesfully'
        ],200);
    }
}
