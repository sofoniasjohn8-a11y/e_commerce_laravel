<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->get();
        if ($products) {
            return response()->json([
                'status' => 200,
                'message' => 'Products retrieved successfully',
                'data' => $products
            ]);
        }
        return response()->json([
            'status' => 200,
            'message' => 'No products found',
            'data' => []
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric',
            // 'discount_price' => 'nullable|numeric',
            'description' => 'required|string',
            'short_description' => 'required|string',
            // 'image' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'qty' => 'required|integer',
            'sku' => 'required|unique:products,sku|max:100',
            //'barcode' => 'nullable|string|max:100',
            'status' => 'integer',
            // 'is_featured' => 'in:yes,no',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create($validator->validated());

        return response()->json([
            'status' => 200,
            'message' => 'Product Added Successfully',
            'data' => $product
        ], 200);
    }

    public function show($id)
    {
        $product = Product::find($id);
        if ($product) {
            return response()->json([
                'status' => 200,
                'message' => 'Product retrieved successfully',
                'data' => $product
            ]);
        }
        return response()->json([
            'status' => 404,
            'message' => 'Product not found'
        ], 404);
    }

   
public function update(Request $request, $id)
{
    $product = Product::find($id);
    if (! $product) {
        return response()->json([
            'status'=>404,
            'message'=>'Product not found'], 
        404);
    }

    $rules = [
        'title'            => 'required|string|max:255',
        'price'            => 'required|numeric',
        'description'      => 'required|string',
        'short_description'=> 'required|string',
        'category_id'      => 'required|exists:categories,id',
        'brand_id'         => 'required|exists:brands,id',
        'qty'              => 'required|integer',
        'sku'              => [
            'required','max:100',
            Rule::unique('products','sku')->ignore($id),
        ],
        'status'           => 'integer',
    ];

    $validated = Validator::make($request->all(), $rules)->validate();

    $product->update($validated);

    return response()->json([
        'status'  => 200,
        'message' => 'Product Updated Successfully',
        'data'    => $product,
    ], 200);
}
    public function destroy($id)
    {
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'status'=>400,
                'message'=>'The Product is not Found'
            ],400);
        }
        $product->delete();
        return response()->json([
            'status'=>200,
            'message'=>'The Product has been deleted successfully'
        ],200);
    
    }
}
