<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogsController extends Controller
{
    public function index()
    {
        $blogs = Blog::all()->map(function ($blog) {
            $blog->image_url = Storage::url($blog->image_path);
            return $blog;
        });

        return response()->json($blogs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $request->file('image')->store('images', 'public');

        $blog = Blog::create([
            'title' => $request->title,
            'category' => $request->category,
            'content' => $request->content,
            'image_path' => $imagePath,
        ]);

        $blog->image_url = Storage::url($imagePath);

        return response()->json($blog, 201);
    }

    public function show(Blog $blog)
    {
        $blog->image_url = Storage::url($blog->image_path);
        return response()->json($blog);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);
    
        $blog = Blog::findOrFail($id);
    
        // Update the blog data (without handling image)
        $blog->update($request->only('title', 'category', 'content'));
    
        $blog->save();
    
        return response()->json([
            'message' => 'Blog updated successfully',
            'blog' => $blog,
        ]);
    }
    
    public function destroy(Blog $blog)
    {
        if ($blog->image_path) {
            Storage::disk('public')->delete($blog->image_path);
        }
        $blog->delete();

        return response()->json(['message' => 'Blog deleted successfully'], 204);
    }
}
