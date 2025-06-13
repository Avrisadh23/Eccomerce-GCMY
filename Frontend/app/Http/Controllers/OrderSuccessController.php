<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class OrderSuccessController extends Controller
{
    protected $otherBackendUrl = 'http://127.0.0.1:8000'; // URL of the other backend project

    public function show()
    {
        $orderId = session('order_id');
        if (!$orderId) {
            return redirect()->route('orders.index')
                ->with('error', 'Order tidak ditemukan.');
        }

        try {
            // Fetch order data from the other backend
            $response = Http::get($this->otherBackendUrl . '/api/orders/' . $orderId);
            
            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['data'])) {
                    return view('orders.success')->with('order', $result['data']);
                }
            }
            
            // If failed to fetch from other backend, use session data as fallback
            if (session('order')) {
                return view('orders.success')->with('order', session('order'));
            }

            return redirect()->route('orders.index')
                ->with('error', 'Gagal mengambil data pesanan.');
        } catch (\Exception $e) {
            // If error occurs, use session data as fallback
            if (session('order')) {
                return view('orders.success')->with('order', session('order'));
            }

            return redirect()->route('orders.index')
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }
} 