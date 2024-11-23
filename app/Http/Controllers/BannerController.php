<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    // Retrieve all banners
    public function index()
    {
        $banners = Banner::all();
        return response()->json($banners, 200);
    }

    // Store a new banner
// Inside your store method
public function store(Request $request)
{
    // Validate incoming data
    $validatedData = $request->validate([
        'title' => 'string|required|max:255',
        'description' => 'string|nullable|max:500',
        'image' => 'nullable|string', // Image is nullable here
        'button_text' => 'string|nullable|max:255',
        'link' => 'string|nullable|url|max:255',
    ]);

    // If an image is uploaded
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('banners', 'public');  // Store image in 'public' folder
        $validatedData['image'] = $imagePath;  // Save image path to the database
    }

    // Create the new banner with the validated data
    $banner = Banner::create($validatedData);

    return response()->json([
        'message' => 'Banner created successfully!',
        'banner' => $banner,
    ], 201); // 201 for "Created"
}


    // Update an existing banner
    public function update(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);

            $validatedData = $request->validate([
                'title' => 'string|nullable|max:255',
                'description' => 'string|nullable|max:500',
                'image' => 'string|nullable|url',
                'button_text' => 'string|nullable|max:255',
                'link' => 'string|nullable|url|max:255',
            ]);

            $banner->update($validatedData);

            return response()->json([
                'message' => 'Banner updated successfully!',
                'banner' => $banner,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Banner not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }

    // Delete a banner
    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->delete();

            return response()->json(['message' => 'Banner deleted successfully!'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Banner not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }
}
