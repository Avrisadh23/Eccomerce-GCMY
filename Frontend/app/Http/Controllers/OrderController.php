<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    protected $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = env('ORDER_SERVICE_EXTERNAL_URL');
    }
    public function index()
    {
        $user = Session::get('user');
        
        try {
            $response = Http::get($this->apiBaseUrl . '/orders', [
                'user_id' => $user['id']
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                if ($result['status'] === 'success') {
                    return view('orders.index', [
                        'orders' => $result['data']
                    ]);
                }
            }
            
            return back()->with('error', 'Gagal mengambil data pesanan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    public function show($id)
    {
        $user = Session::get('user');
        
        try {
            $response = Http::get($this->apiBaseUrl . '/orders/' . $id);
            
            if ($response->successful()) {
                $result = $response->json();
                if ($result['status'] === 'success') {
                    // Verify that this order belongs to the current user
                    if ($result['data']['user_id'] !== $user['id']) {
                        return redirect()->route('orders.index')
                            ->with('error', 'Anda tidak memiliki akses ke pesanan ini.');
                    }
                    
                    return view('orders.show', [
                        'order' => $result['data']
                    ]);
                }
            }
            
            return back()->with('error', 'Pesanan tidak ditemukan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }
} 