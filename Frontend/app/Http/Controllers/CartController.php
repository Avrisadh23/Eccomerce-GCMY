<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class CartController extends Controller
{
    protected $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = env('PRODUCT_SERVICE_EXTERNAL_URL');
    }
    
    public function index()
    {
        $cart = Session::get('cart', []);
        $cartItems = [];
        $total = 0;

        // Ambil detail produk untuk setiap item di keranjang
        foreach ($cart as $productId => $item) {
            try {
                $response = Http::get($this->apiBaseUrl . '/products/' . $productId);
                if ($response->successful()) {
                    $product = $response->json()['data'];
                    $cartItems[] = [
                        'id' => $productId,
                        'nama' => $product['nama'],
                        'harga' => $product['harga'],
                        'gambar' => $product['gambar'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $product['harga'] * $item['quantity']
                    ];
                    $total += $product['harga'] * $item['quantity'];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return view('cart.index', [
            'cartItems' => $cartItems,
            'total' => $total
        ]);
    }

    public function add($productId, Request $request)
    {
        $quantity = $request->input('quantity', 1);
        
        // Ambil cart dari session atau buat array baru jika belum ada
        $cart = Session::get('cart', []);
        
        // Jika produk sudah ada di cart, tambahkan quantity
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            // Jika produk belum ada di cart, tambahkan sebagai item baru
            $cart[$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity
            ];
        }
        
        // Simpan cart kembali ke session
        Session::put('cart', $cart);
        
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang'
        ]);
    }

    public function remove($productId)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus dari keranjang'
        ]);
    }

    public function update($productId, Request $request)
    {
        $quantity = $request->input('quantity');
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            if ($quantity > 0) {
                $cart[$productId]['quantity'] = $quantity;
            } else {
                unset($cart[$productId]);
            }
            Session::put('cart', $cart);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui'
        ]);
    }

    public function clear()
    {
        Session::forget('cart');
        
        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan'
        ]);
    }
} 