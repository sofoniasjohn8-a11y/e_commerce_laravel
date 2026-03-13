<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Illuminate\Validation\Rule;
use App\Models\TempImage;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
            'description' => 'required|string',
            'short_description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'qty' => 'required|integer',
            'sku' => 'required|unique:products,sku|max:100',
            'status' => 'integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $product = Product::create($validator->validated());
        if (!empty($request->gallery)) {
            // Ensure directories exist and log results
            $largeDirResult = Storage::disk('public')->makeDirectory('products/large', 0755, true);
            $smallDirResult = Storage::disk('public')->makeDirectory('products/small', 0755, true);
            \Log::debug('Directory creation', ['large' => $largeDirResult, 'small' => $smallDirResult]);

            foreach ($request->gallery as $key => $imageId) {
                \Log::debug('Processing gallery item', ['key' => $key, 'imageId' => $imageId]);
                try {
                    $tempImage = TempImage::find($imageId);
                    if (!$tempImage) {
                        \Log::warning('TempImage record not found', ['id' => $imageId]);
                        continue;
                    }

                    $extArray = explode('.', $tempImage->name);
                    $ext = end($extArray);

                    $imageName = $product->id . '-' . time() . '-' . $key . '.' . $ext;

                    // Get full path to temp image
                    $tempImageFullPath = Storage::disk('public')->path($tempImage->path);

                    if (!file_exists($tempImageFullPath)) {
                        \Log::warning('Temp image file missing on disk', ['path' => $tempImageFullPath]);
                        continue;
                    }

                    // Create large version (limit both width and height to 800px)
                    $largePath = 'products/large/' . $imageName;
                    $largeFullPath = Storage::disk('public')->path($largePath);
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read($tempImageFullPath);
                    $img->resize(800, 800, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $img->save($largeFullPath);
                    if (!file_exists($largeFullPath)) {
                        \Log::error("Large image not saved: $largeFullPath");
                    } else {
                        \Log::debug('Large image saved', ['path' => $largeFullPath]);
                    }

                    // Create small version (resize to 400px width, keep aspect ratio)
                    $smallPath = 'products/small/' . $imageName;
                    $smallFullPath = Storage::disk('public')->path($smallPath);
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read($tempImageFullPath);
                    $img->resize(400, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $img->save($smallFullPath);
                    if (!file_exists($smallFullPath)) {
                        \Log::error("Small image not saved: $smallFullPath");
                    } else {
                        \Log::debug('Small image saved', ['path' => $smallFullPath]);
                    }

                    // Set the main product image to the large version
                    if ($key == 0) {
                        $product->image = $largePath;
                        $product->save();
                    }

                    // Do not delete temp images; keep records/files indefinitely
                    // Storage::disk('public')->delete($tempImage->path);
                    // $tempImage->delete();
                } catch (\Exception $e) {
                    // Log the error for debugging
                    \Log::error('Error processing product image: ' . $e->getMessage());
                    continue;
                }
            }
        }
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