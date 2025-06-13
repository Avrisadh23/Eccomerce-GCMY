<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    protected $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = env('PRODUCT_SERVICE_EXTERNAL_URL');
    }
    public function index(Request $request)
    {
        try {
            $response = Http::get($this->apiBaseUrl . '/products', [
                'page' => $request->get('page', 1),
                'per_page' => 12,
                'kategori_id' => $request->get('kategori_id')
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                if ($result['status'] === 'success') {
                    $products = $result['data']['products'] ?? [];
                    return view('products.index', [
                        'products' => $products,
                        'pagination' => [
                            'total' => $result['data']['total'] ?? 0,
                            'pages' => $result['data']['pages'] ?? 1,
                            'current_page' => $result['data']['current_page'] ?? 1,
                        ]
                    ]);
                }
            }
            
            return back()->with('error', 'Unable to fetch products.');
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to connect to product service.');
        }
    }

    public function show($id)
    {
        try {
            $response = Http::get($this->apiBaseUrl . '/products/' . $id);
            
            if ($response->successful()) {
                $result = $response->json();
                if ($result['status'] === 'success') {
                    return view('products.show', ['product' => $result['data']]);
                }
            }
            
            return back()->with('error', 'Produk tidak ditemukan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Tidak dapat terhubung ke layanan produk.');
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('query');
            $response = Http::get($this->apiBaseUrl . '/products', [
                'search' => $query,
                'page' => $request->get('page', 1),
                'per_page' => 12
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                if ($result['status'] === 'success') {
                    return view('products.index', [
                        'products' => $result['data'],
                        'query' => $query
                    ]);
                }
            }
            
            return back()->with('error', 'Unable to search products.');
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to connect to product service.');
        }
    }
}