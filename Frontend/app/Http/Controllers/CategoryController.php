<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CategoryController extends Controller
{
    protected $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = env('PRODUCT_SERVICE_EXTERNAL_URL');
    }

    public function index()
    {
        // Gunakan data dummy untuk sementara
        $categories = $this->getDummyCategories();
        return view('categories.index', compact('categories'));

        /* Uncomment ini jika API sudah siap
        try {
            $response = Http::get($this->apiBaseUrl . '/categories');
            
            if ($response->successful()) {
                $result = $response->json();
                $categories = $result['data'] ?? [];
                return view('categories.index', compact('categories'));
            }
            
            return back()->with('error', 'Unable to fetch categories.');
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to connect to category service.');
        }
        */
    }

    public function show($id)
    {
        // Gunakan data dummy untuk sementara
        $categories = $this->getDummyCategories();
        $category = collect($categories)->firstWhere('id', (int)$id);
        
        if (!$category) {
            return back()->with('error', 'Category not found.');
        }

        // Data dummy untuk produk dalam kategori
        $products = [
            'data' => $this->getDummyProductsByCategory($id),
            'current_page' => 1,
            'total' => 2,
            'pages' => 1
        ];

        return view('categories.show', compact('category', 'products'));

        /* Uncomment ini jika API sudah siap
        try {
            $categoryResponse = Http::get($this->apiBaseUrl . '/categories/' . $id);
            $productsResponse = Http::get($this->apiBaseUrl . '/products', [
                'category_id' => $id,
                'page' => request('page', 1),
                'per_page' => 12
            ]);
            
            if ($categoryResponse->successful() && $productsResponse->successful()) {
                $categoryResult = $categoryResponse->json();
                $productsResult = $productsResponse->json();
                
                $category = $categoryResult['data'] ?? null;
                $products = $productsResult;
                
                if (!$category) {
                    return back()->with('error', 'Category not found.');
                }
                
                return view('categories.show', compact('category', 'products'));
            }
            
            return back()->with('error', 'Unable to fetch category details.');
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to connect to category service.');
        }
        */
    }

    private function getDummyCategories()
    {
        return [
            [
                'id' => 1,
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets'
            ],
            [
                'id' => 2,
                'name' => 'Fashion',
                'description' => 'Clothing and accessories'
            ],
            [
                'id' => 3,
                'name' => 'Books',
                'description' => 'Books and literature'
            ]
        ];
    }

    private function getDummyProductsByCategory($categoryId)
    {
        $products = [
            // Electronics (ID: 1)
            1 => [
                [
                    'id' => 1,
                    'nama' => 'Smartphone X Pro',
                    'harga' => 4999000,
                    'deskripsi' => 'Smartphone canggih dengan kamera 108MP',
                    'gambar' => 'https://via.placeholder.com/150',
                    'stok' => 50
                ],
                [
                    'id' => 2,
                    'nama' => 'Laptop UltraBook Pro',
                    'harga' => 12999000,
                    'deskripsi' => 'Laptop tipis dan ringan dengan performa tinggi',
                    'gambar' => 'https://via.placeholder.com/150',
                    'stok' => 25
                ]
            ],
            // Fashion (ID: 2)
            2 => [
                [
                    'id' => 3,
                    'nama' => 'Kemeja Casual Premium',
                    'harga' => 299000,
                    'deskripsi' => 'Kemeja casual dengan bahan premium',
                    'gambar' => 'https://via.placeholder.com/150',
                    'stok' => 100
                ],
                [
                    'id' => 4,
                    'nama' => 'Celana Jeans Slim Fit',
                    'harga' => 399000,
                    'deskripsi' => 'Celana jeans dengan potongan slim fit',
                    'gambar' => 'https://via.placeholder.com/150',
                    'stok' => 75
                ]
            ],
            // Books (ID: 3)
            3 => [
                [
                    'id' => 5,
                    'nama' => 'Novel Best Seller 2023',
                    'harga' => 98000,
                    'deskripsi' => 'Novel terlaris tahun 2023',
                    'gambar' => 'https://via.placeholder.com/150',
                    'stok' => 200
                ],
                [
                    'id' => 6,
                    'nama' => 'Buku Pengembangan Diri',
                    'harga' => 149000,
                    'deskripsi' => 'Buku panduan pengembangan diri',
                    'gambar' => 'https://via.placeholder.com/150',
                    'stok' => 150
                ]
            ]
        ];

        return $products[$categoryId] ?? [];
    }
} 