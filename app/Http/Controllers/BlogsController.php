<?php

namespace App\Http\Controllers;

use App\Models\Blog;  // Corrected to match your model name (singular)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::all();
    
        // Loop through the blogs and attach the full URL for the image
        $blogs->each(function($blog) {
            $blog->image_url = Storage::url($blog->image_path);  // Generate full URL for the image
        });
    
        return response()->json($blogs);
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request: make sure an image is required
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',  // Make image required
        ]);
    
        // Generate a custom filename for the image
        $imageName = time() . '.' . $request->file('image')->getClientOriginalExtension();  // Use current time to create unique file name
        
        // Store the image with the custom name in the 'images' folder in the public disk
        $imagePath = $request->file('image')->storeAs('images', $imageName, 'public');  // Store image with custom filename
    
        // Store the blog post in the database
        $blog = Blog::create([
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $imagePath,  // Store the image path
        ]);
    
        // Generate the full URL for the image
        $blog->image_url = Storage::url($imagePath);  // Correct image URL
    
        // Return the created blog
        return response()->json($blog, 201);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        return response()->json($blog);  // Return a single blog post
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        return view('blogs.edit', compact('blog'));  // You can render an edit form here
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload if there is one
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($blog->image_path) {
                Storage::disk('public')->delete($blog->image_path);
            }
            // Store the new image
            $blog->image_path = $request->file('image')->store('images', 'public');
        }

        // Update the blog
        $blog->update([
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $blog->image_path,
        ]);

        return response()->json($blog);  // Return the updated blog
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        // Delete the image if it exists
        if ($blog->image_path) {
            Storage::disk('public')->delete($blog->image_path);
        }

        // Delete the blog record
        $blog->delete();

        return response()->json(null, 204);  // Return a no content response
    }
}
