<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\TempImage;
use App\Models\ProductImage;
use App\Models\ProductSize;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    
    public function index()
    {
        $products = Product::with(['product_images','product_sizes'])->orderBy('created_at', 'DESC')->get();
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
            'title'             => 'required|string|max:255',
            'price'             => 'required|numeric',
            'description'       => 'required|string',
            'short_description' => 'required|string',
            'category_id'       => 'required|exists:categories,id',
            'brand_id'          => 'required|exists:brands,id',
            'qty'                => 'required|integer',
            'sku'                => 'required|unique:products,sku|max:100',
            'status'             => 'integer',
            
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // Use a Transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // 1. Create the Product
            $product = Product::create($validator->validated());

            if (!empty($request->gallery)) {
                
                // 2. Setup Directories (relative to 'public' disk root)
                // This saves to storage/app/public/products/...
                Storage::disk('public')->makeDirectory('products/large');
                Storage::disk('public')->makeDirectory('products/small');

                $manager = new ImageManager(new Driver());

                foreach ($request->gallery as $key => $imageId) {
                    $tempImage = TempImage::find($imageId);
                    
                    if (!$tempImage) {
                        Log::warning("TempImage ID {$imageId} not found.");
                        continue;
                    }

                    // Prepare file details
                    $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                    $imageName = $product->id . '-' . time() . '-' . $key . '.' . $ext;

                    // Source path
                    $tempImageFullPath = Storage::disk('public')->path($tempImage->path);

                    if (!file_exists($tempImageFullPath)) {
                        Log::error("Source file missing: " . $tempImageFullPath);
                        continue;
                    }

                    // 3. Process Large Image (800px width, auto height)
                    $largePath = 'products/large/' . $imageName;
                    $largeFullPath = Storage::disk('public')->path($largePath);
                    
                    $imgLarge = $manager->read($tempImageFullPath);
                    $imgLarge->scale(width: 800); 
                    $imgLarge->save($largeFullPath);

                    // 4. Process Small Image (400px width, auto height)
                    $smallPath = 'products/small/' . $imageName;
                    $smallFullPath = Storage::disk('public')->path($smallPath);
                    
                    $imgSmall = $manager->read($tempImageFullPath);
                    $imgSmall->scale(width: 400);
                    $imgSmall->save($smallFullPath);

                    // 5. Save to ProductImage table
                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = $imageName;
                    $productImage->save();

                    // 6. Set first image as main product thumbnail
                    if ($key == 0) {
                       $product->image = 'products/large/' . $imageName; // Store name only or 'products/large/'.$imageName
                        $product->save();
                    }
                }
            }
            
            if(!empty($request->sizes)){
                foreach($request->sizes as $sizeId){
                $productSizes = new ProductSize();
                $productSizes->product_id = $product->id;
                $productSizes->size_id = $sizeId;
                $productSizes->save();
                }
            }
                // Clean up Temp Images
                TempImage::whereIn('id', $request->gallery)->delete();
            
            $productSize = $product->product_sizes()->pluck('size_id');
            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Product Added Successfully',
                'data' => $product,
                'product_sizes' => $productSize
            ], 200);
         
        } catch(\Exception $e){
            DB::rollBack();
            Log::error('Product Store Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $product = Product::
        with(['product_images', 'product_sizes'])
        ->find($id);
        $productSize = $product->product_sizes()->pluck('size_id');
        if ($product) {
            return response()->json([
                'status' => 200,
                'message' => 'Product retrieved successfully',
                'data' => $product,
                'productSize' => $productSize
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
            'title'             => 'required|string|max:255',
            'price'             => 'required|numeric',
            'description'       => 'required|string',
            'short_description' => 'required|string',
            'category_id'       => 'required|exists:categories,id',
            'brand_id'          => 'required|exists:brands,id',
            'qty'                => 'required|integer',
            'sku'                => 'required|unique:products,sku,' . $product->id,
            'status'             => 'integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // Use a Transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // 1. Create the Product
            $product->update($validator->validated());

            if (!empty($request->gallery)) {
                
                // 2. Setup Directories (relative to 'public' disk root)
                // This saves to storage/app/public/products/...
                Storage::disk('public')->makeDirectory('products/large');
                Storage::disk('public')->makeDirectory('products/small');

                $manager = new ImageManager(new Driver());

                foreach ($request->gallery as $key => $imageId) {
                    $tempImage = TempImage::find($imageId);
                    
                    if (!$tempImage) {
                        Log::warning("TempImage ID {$imageId} not found.");
                        continue;
                    }

                    // Prepare file details
                    $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                    $imageName = $product->id . '-' . time() . '-' . $key . '.' . $ext;

                    // Source path
                    $tempImageFullPath = Storage::disk('public')->path($tempImage->path);

                    if (!file_exists($tempImageFullPath)) {
                        Log::error("Source file missing: " . $tempImageFullPath);
                        continue;
                    }

                    // 3. Process Large Image (800px width, auto height)
                    $largePath = 'products/large/' . $imageName;
                    $largeFullPath = Storage::disk('public')->path($largePath);
                    
                    $imgLarge = $manager->read($tempImageFullPath);
                    $imgLarge->scale(width: 800); 
                    $imgLarge->save($largeFullPath);

                    // 4. Process Small Image (400px width, auto height)
                    $smallPath = 'products/small/' . $imageName;
                    $smallFullPath = Storage::disk('public')->path($smallPath);
                    
                    $imgSmall = $manager->read($tempImageFullPath);
                    $imgSmall->scale(width: 400);
                    $imgSmall->save($smallFullPath);

                    // 5. Save to ProductImage table
                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = $imageName;
                    $productImage->save();

                    // 6. Set first image as main product thumbnail
                    if ($key == 0) {
                       $product->image = 'products/large/' . $imageName; // Store name only or 'products/large/'.$imageName
                        $product->save();
                    }
                }
            }
             if(!empty($request->sizes)){
                $productSize = ProductSize::where('product_id',$product->id)->delete();
                foreach($request->sizes as $sizeId){
                $productSizes = new ProductSize();
                $productSizes->product_id = $product->id;
                $productSizes->size_id = $sizeId;
                $productSizes->save();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Product updated Successfully',
                'data' => $product
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product Store Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }

        
    }

    public function destroy($id)
{
    // 1. Find the product with its images loaded
    $product = Product::with('product_images')->find($id);

    if (!$product) {
        return response()->json([
            'status' => 404,
            'message' => 'The Product is not Found'
        ], 404);
    }

    // 2. Loop through the collection (no parentheses)
    if ($product->product_images->isNotEmpty()) {
        foreach ($product->product_images as $productImage) {
            // Use Storage::disk('public') to delete files correctly
            Storage::disk('public')->delete('products/large/' . $productImage->image);
            Storage::disk('public')->delete('products/small/' . $productImage->image);
        }
    }

    // 3. Delete the product (this will delete related images in DB if you have cascade delete)
    $product->delete();

    return response()->json([
        'status' => 200,
        'message' => 'The Product and its images have been deleted successfully'
    ], 200);
   }
    public function setDefaultImage($pro_image_id)
    {
        $productImage = ProductImage::find($pro_image_id);
        if(!$productImage){
            return response()->json([
                'status'=>400,
                'message'=>'The Product Image is not Found'
            ],400);
        }
        $product = Product::find($productImage->product_id);
        if(!$product){
            return response()->json([
                'status'=>400,
                'message'=>'The Product is not Found'
            ],400);
        }
        $product->image = $productImage->image;
        // $product->image = $productImage->image_url;
        $product->save();
        return response()->json([
            'status'=>200,
            'message'=>'The Product Default Image has been changed successfully',
            'data'=>$product
        ],200);


    }
  public function removeImage($pro_image_id)
{
    // 1. Try to find it in ProductImage first
    $productImage = ProductImage::find($pro_image_id);

    if (!$productImage) {
        // 2. If not found, check TempImage (useful for cleanup during upload)
        $tempImage = TempImage::find($pro_image_id);
        if (!$tempImage) {
            return response()->json(['status' => 404, 'message' => 'Image not found'], 404);
        }

        // Delete temp file and record
        Storage::disk('public')->delete($tempImage->path);
        $tempImage->delete();

        return response()->json(['status' => 200, 'message' => 'Temp image deleted']);
    }

    // 3. Logic for existing Product Images
    $product = Product::find($productImage->product_id);
    
    // Use Storage facade with the correct relative path
    Storage::disk('public')->delete('products/large/' . $productImage->image);
    Storage::disk('public')->delete('products/small/' . $productImage->image);

    // 4. Update Product Thumbnail if this was the main image
    // Using 'str_contains' or checking both possible formats ensures it works
    if ($product && ($product->image == $productImage->image || str_ends_with($product->image, $productImage->image))) {
        $product->image = null; 
        $product->save();
    }

    $productImage->delete();

    return response()->json([
        'status' => 200,
        'message' => 'The Product Image has been deleted successfully',
        'data' => $product
    ], 200);
}
}