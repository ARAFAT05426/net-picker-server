<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class AffiliateProductService extends Controller
{
    private function standardizeProductData($product)
    {
        return [
            'id' => $product['id'] ?? null,
            'name' => $product['name'] ?? $product['title'] ?? 'Untitled',
            'description' => $product['description'] ?? '',
            'category' => $product['category'] ?? 'Unknown',
            'price' => $product['price'] ?? 0,
            'image_url' => $product['images'] ?? $product['image'] ?? null,
            'affiliate_link' => $product['affiliate_link'] ?? $product['url'] ?? null,
        ];
    }

    public function fetchFromDummyJson()
    {
        $response = Http::get('https://dummyjson.com/products');
        $products = $response->json()['products'] ?? [];


        return array_map([$this, 'standardizeProductData'], $products);
    }

    public function fetchFromFakeStore()
    {
        $response = Http::get('https://fakestoreapi.com/products');
        $products = $response->json() ?? [];

        return array_map([$this, 'standardizeProductData'], $products);
    }

    public function fetchFromJsonPlaceholder()
    {
        $response = Http::get('https://jsonplaceholder.typicode.com/posts');
        $products = $response->json() ?? [];

        return array_map([$this, 'standardizeProductData'], $products);
    }

    // Fetch a single product by ID from DummyJSON API
    public function fetchSingleFromDummyJson($id)
    {
        $response = Http::get("https://dummyjson.com/products/{$id}");
        $product = $response->json();

        return $this->standardizeProductData($product);
    }

    // Fetch a single product by ID from FakeStore API
    public function fetchSingleFromFakeStore($id)
    {
        $response = Http::get("https://fakestoreapi.com/products/{$id}");
        $product = $response->json();

        return $this->standardizeProductData($product);
    }

    // Fetch a single product by ID from JSONPlaceholder API (if applicable)
    public function fetchSingleFromJsonPlaceholder($id)
    {
        $response = Http::get("https://jsonplaceholder.typicode.com/posts/{$id}");
        $product = $response->json();

        return $this->standardizeProductData($product);
    }
}
