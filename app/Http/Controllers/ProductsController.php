<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $category = $request->input('category', '');
        $limit = $request->input('limit', 10);  // Default to 10 items if no limit
        $page = $request->input('page', 1);     // Default to page 1

        // Initialize empty collection for products
        $allProducts = collect();

        // Fetch and process data from DummyJSON API if successful
        $dummyJsonResponse = Http::retry(3, 100)->get("https://dummyjson.com/products/search?q=$search&category=$category&limit=$limit");
        if ($dummyJsonResponse->successful()) {
            $dummyJsonProducts = collect($dummyJsonResponse->json()['products'])->map(function ($product) {
                return [
                    'source' => 'dummyjson',
                    'id' => $product['id'],
                    'name' => $product['title'],
                    'description' => $product['description'],
                    'category' => $product['category'],
                    'price' => $product['price'],
                    'brand' => $product['brand'],
                    'stock' => $product['stock'],
                    'discountPercentage' => $product['discountPercentage'],
                    'image_url' => $product['images'][0] ?? null, // Use the first image from the images array
                    'rating' => $product['rating'],
                ];
            });
            $allProducts = $allProducts->merge($dummyJsonProducts);
        } else {
            Log::error("DummyJSON API Error: " . $dummyJsonResponse->status() . ' - ' . $dummyJsonResponse->body());
        }

        // Fetch and process data from FakeStore API if successful
        $fakeStoreApiResponse = Http::retry(3, 100)->get("https://fakestoreapi.com/products?search=$search&category=$category&limit=$limit");
        if ($fakeStoreApiResponse->successful()) {
            $fakeStoreApiProducts = collect($fakeStoreApiResponse->json())->map(function ($product) {
                return [
                    'source' => 'fakestore',
                    'id' => $product['id'],
                    'name' => $product['title'],
                    'description' => $product['description'],
                    'category' => $product['category'],
                    'price' => $product['price'],
                    'image_url' => $product['image'] ?? null,
                    'rating' => $product['rating'] ?? null,
                ];
            });
            $allProducts = $allProducts->merge($fakeStoreApiProducts);
        } else {
            Log::error("FakeStore API Error: " . $fakeStoreApiResponse->status() . ' - ' . $fakeStoreApiResponse->body());
        }

        // Check if any products were retrieved
        if ($allProducts->isEmpty()) {
            return response()->json(['error' => 'Failed to fetch products from external sources'], 500);
        }

        // Apply category filtering if provided
        if ($category) {
            $allProducts = $allProducts->where('category', $category);
        }

        // Paginate the collection manually
        $paginatedProducts = $allProducts->forPage($page, $limit)->values();

        // Return paginated response with metadata
        return response()->json([
            'total' => $allProducts->count(),
            'current_page' => $page,
            'per_page' => $limit,
            'last_page' => ceil($allProducts->count() / $limit),
            'products' => $paginatedProducts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($productId)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($productId)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $productId)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($productId)
    {
        //
    }
}
