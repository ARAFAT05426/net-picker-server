<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $externalProductService;

    public function __construct(AffiliateProductService $externalProductService)
    {
        $this->externalProductService = $externalProductService;
    }

    /**
     * Display a listing of the products or search results.
     */
    public function index(Request $request)
    {
        // Fetch data from external APIs
        $dummyJsonProducts = $this->externalProductService->fetchFromDummyJson();
        $fakeStoreProducts = $this->externalProductService->fetchFromFakeStore();

        // Combine the products from all APIs
        $products = array_merge($dummyJsonProducts, $fakeStoreProducts);

        // Apply filters based on request parameters
        if ($request->has('category')) {
            $products = array_filter($products, function ($product) use ($request) {
                return stripos($product['category'], $request->category) !== false;
            });
        }

        // Apply search by product name
        if ($request->has('search')) {
            $products = array_filter($products, function ($product) use ($request) {
                return stripos($product['title'], $request->search) !== false;
            });
        }

        // Apply sorting
        if ($request->has('sort_by')) {
            $sortBy = $request->sort_by;
            $direction = $request->get('direction', 'asc');
            usort($products, function ($a, $b) use ($sortBy, $direction) {
                if ($direction === 'asc') {
                    return strcmp($a[$sortBy], $b[$sortBy]);
                } else {
                    return strcmp($b[$sortBy], $a[$sortBy]);
                }
            });
        }

        // Paginate the results if needed (optional)
        $perPage = $request->get('per_page', 10);
        $products = array_slice($products, 0, $perPage);

        return response()->json($products);
    }

    /**
     * Search Suggestions (Optional).
     */
    public function searchSuggestions(Request $request)
    {
        $query = $request->get('q', '');

        // Return an empty array if no query is provided
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        // Fetch matching products
        $suggestions = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('category', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'category') // Return only necessary fields
            ->take(10) // Limit to 10 suggestions
            ->get();

        return response()->json($suggestions);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'affiliate_link' => 'nullable|url',
            'image_url' => 'nullable|url',
            'category' => 'nullable|string|max:100',
        ]);

        $product = Product::create($validated);
        return response()->json(['message' => 'Product created successfully', 'product' => $product]);
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        // Fetch product from the external API based on ID
        $dummyJsonProduct = $this->externalProductService->fetchSingleFromDummyJson($id);
        $fakeStoreProduct = $this->externalProductService->fetchSingleFromFakeStore($id);

        // Combine the results from both APIs
        $product = array_merge($dummyJsonProduct, $fakeStoreProduct);

        // If no product found, return 404 response
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }


    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'affiliate_link' => 'nullable|url',
            'image_url' => 'nullable|url',
            'category' => 'nullable|string|max:100',
        ]);

        $product->update($validated);
        return response()->json(['message' => 'Product updated successfully', 'product' => $product]);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
