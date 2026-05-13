<?php

namespace App\Http\Controllers\Admin; // Check if 'Admin' should be capitalized

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TempImageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // 1. Store the file using the public disk
            $path = $file->storeAs('temp_images', $filename, 'public');
            
            // Get the absolute path for Intervention to read
            $absolutePath = Storage::disk('public')->path($path);

            try {
                // 2. Process the image (Intervention v3)
                $manager = new ImageManager(new Driver());
                $img = $manager->read($absolutePath);
                
                // Apply transformations (e.g., crop to 400x450)
                $img->cover(400, 450);
                
                // Save back to the same path
                $img->save($absolutePath);

                // 3. Save to database
                $tempImage = new TempImage();
                $tempImage->name = $filename;
                $tempImage->path = $path;
                $tempImage->save();
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Image uploaded successfully',
                    'image_id' => $tempImage->id,
                    'image_url' => asset('storage/' . $path) 
                ]);

            } catch (\Exception $e) {
                // If processing fails, delete the uploaded file and return error
                Storage::disk('public')->delete($path);
                return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
            }
        }

        return response()->json([
            'status' => 400,
            'message' => 'No image file provided'
        ], 400);
    }
    public function index(){
        $tempImages = TempImage::all();
        if(!$tempImages){
            return response()->json([
                'status' => 200,
                'message' => 'No temporary images found',
                'data' => []
            ]);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Temporary images retrieved successfully',
            'data' => $tempImages
        ]);
    }
    public function show($id){
        $tempImage = TempImage::find($id);
        if (!$tempImage) {
            return response()->json([
                'status' => 404,
                'message' => 'Image not found'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Image retrieved successfully',
            'data' => $tempImage
        ]);
    }
    public function destroy($id)
    {
        
        $tempImage = TempImage::find($id);
        if (!$tempImage) {
            return response()->json([
                'status' => 404,
                'message' => 'Image not found'
            ], 404);
        }

        // Delete the file from storage
        Storage::disk('public')->delete($tempImage->path);

        // Delete the database record
        $tempImage->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Image deleted successfully'
        ]);
    }
}