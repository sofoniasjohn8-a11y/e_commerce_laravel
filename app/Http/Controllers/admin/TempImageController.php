<?php

namespace App\Http\Controllers\admin;

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
        // Validate request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Store in public disk's temp_images directory
            $path = $file->storeAs('temp_images', $filename, 'public');
            
            // Save to database
            $tempImage = new TempImage();
            $tempImage->name = $filename;
            $tempImage->path = $path;
            $tempImage->save();
            
            // Process the uploaded image
            $manager = new ImageManager(Driver::class);
            $img = $manager->read(Storage::disk('public')->path($path));  // Read the actual uploaded file
            $img->cover(400, 450);  // Resize/crop as intended
            $img->save(Storage::disk('public')->path($path));  // Save back to the same path
            
            return response()->json([
                'status' => 200,
                'message' => 'Image uploaded successfully',
                'data' => $tempImage
            ]);
        }

        return response()->json([
            'status' => 400,
            'message' => 'No image file provided'
        ], 400);
    }
}
