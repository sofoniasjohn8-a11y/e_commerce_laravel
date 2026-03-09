<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Category;
use Illuminate\Support\Facades\Validator; 
class CategoryController extends Controller
{ 
    public function index(){
        $categories = Category::orderBy('created_at','DESC')->get();
        if($categories){
            return response()->json([
            'status' => 200,
            'data'  => $categories
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
        $category1 = new Category();
        $category1->name = $request->name;
        $category1->status = $request->status;
        $category1->save();

        
        return response()->json([
            'status' => 200,
            'data'  => $category1,
            'message'=>'Category Added Successfully'
        ],200);
    
    }
    public function show($id){
        $category = Category::find($id);
        if($category){
            return response()->json([
                'status'=>200,
                'data'=>$category
            ]);
        }
        return response()->json([
            'status'=>400,
            'data'=>[]
        ]);
    }
    public function update($id,Request $request){
        $category = Category::find($id);
        if(!$category){
             return response()->json([
                'message'=>'The Category is not Found'
            ]);
        }
        $category->name = $request->name ? $request->name:$category->name;
        if($request->status == '0'){
             $category->status = 0;
        }
        else{
            $category->status = $request->status  ? $request->status  : $category->status;
        } 
            $category->save();
         return response()->json([
                'status'=>200,
                'data' => $category ,
                'message'=>'Category Updated Successfully'
            ]);
    }
    public function destroy($id){
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'status'=>400,
                'message'=>'The Category is not Found'
            ],400);
        }
        $category->delete();
        return response()->json([
            'status'=>200,
            'message'=>'The Category has been deleted successfully'
        ],200);
    }
}
