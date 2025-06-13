<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    protected $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = env('PRODUCT_SERVICE_EXTERNAL_URL');
    }
    

    public function index()
    {
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong');
        }

        $cartItems = [];
        $total = 0;

        foreach ($cart as $productId => $item) {
            try {
                $response = Http::get($this->apiBaseUrl . '/products/' . $productId);
                if ($response->successful()) {
                    $product = $response->json()['data'];
                    $cartItems[] = [
                        'id' => $productId,
                        'nama' => $product['nama'],
                        'harga' => $product['harga'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $product['harga'] * $item['quantity'],
                        'weight' => $product['weight'] ?? 1000,
                        'origin_city_id' => $product['origin_city_id'] ?? null
                    ];
                    $total += $product['harga'] * $item['quantity'];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Ambil list kota dari location service logistic
        $cities = [];
        try {
            $citiesResponse = Http::get(env('LOCATION_SERVICE_URL', 'http://logistic-location:5001/cities'));
            if ($citiesResponse->successful()) {
                $cities = $citiesResponse->json();
            }
        } catch (\Exception $e) {}

        $user = Session::get('user');

        return view('checkout.index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'user' => $user,
            'cities' => $cities
        ]);
    }

    public function calculateShipping(Request $request)
    {
        try {
            $destination = (int) $request->destination;
            $weight = (int) $request->weight;
            $courier = $request->courier;

            // Ambil origin dari produk pertama di cart
            $cart = Session::get('cart', []);
            $origin = null;
            foreach ($cart as $productId => $item) {
                $response = Http::get($this->apiBaseUrl . '/products/' . $productId);
                if ($response->successful()) {
                    $product = $response->json()['data'];
                    $origin = $product['origin_city_id'] ?? null;
                    break;
                }
            }

            if (!$origin) {
                return response()->json(['error' => 'Origin city tidak ditemukan'], 400);
            }

            // --- Kirim log ke history_service logistic via GraphQL ---
            try {
                $historyGraphqlUrl = env('HISTORY_SERVICE_GRAPHQL_URL', 'http://localhost:5113/graphql');
                $user = Session::get('user');
                $details = [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier
                ];
                $detailsJson = addslashes(json_encode($details, JSON_UNESCAPED_UNICODE));
                $mutation = <<<GQL
                mutation {
                  createHistory(
                    userId: {$user['id']},
                    actionType: "shipping",
                    actionId: 0,
                    details: "{$detailsJson}"
                  ) {
                    id
                    userId
                    actionType
                    actionId
                    details
                    createdAt
                  }
                }
                GQL;

                $historyResponse = Http::post($historyGraphqlUrl, [
                    'query' => $mutation
                ]);
                if (!$historyResponse->successful()) {
                    \Log::error('History Service Log Error', [
                        'status' => $historyResponse->status(),
                        'body' => $historyResponse->body()
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('History Service Log Exception', ['error' => $e->getMessage()]);
            }

            // Kirim query GraphQL ke shipping logistic
            $query = <<<GQL
            query {
                shippingRates(origin: $origin, destination: $destination, weight: $weight, courier: "$courier") {
                    courier
                    service
                    etd
                    cost
                }
            }
            GQL;

            $response = Http::post(env('SHIPPING_SERVICE_GRAPHQL_URL', 'http://localhost:5110/graphql'), [
                'query' => $query
            ]);

            if (!$response->successful() || !isset($response['data']['shippingRates'])) {
                return response()->json(['error' => 'Gagal mengambil ongkos kirim'], 500);
            }

            $shippingOptions = $response['data']['shippingRates'];

            return response()->json([
                'status' => 'success',
                'shipping_options' => $shippingOptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghitung ongkos kirim'
            ], 500);
        }
    }

    public function process(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'alamat' => 'required',
            'kota' => 'required',
            'kode_pos' => 'required',
            'no_telepon' => 'required',
            'shipping_cost' => 'required|numeric',
            'shipping_method' => 'required'
        ]);

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong');
        }

        $user = Session::get('user');
        $cartItems = [];
        $total = 0;

        foreach ($cart as $productId => $item) {
            try {
                $response = Http::get(env('PRODUCT_SERVICE_EXTERNAL_URL') . '/products/' . $productId);
                if ($response->successful()) {
                    $product = $response->json()['data'];
                    $cartItems[] = [
                        'product_id' => $productId,
                        'nama' => $product['nama'],
                        'harga' => $product['harga'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $product['harga'] * $item['quantity']
                    ];
                    $total += $product['harga'] * $item['quantity'];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $order = [
            'id' => 'ORD-' . time(),
            'user_id' => $user['id'],
            'total_amount' => $total + $request->shipping_cost,
            'shipping_address' => [
                'nama_lengkap' => $request->nama_lengkap,
                'alamat' => $request->alamat,
                'kota' => $request->kota,
                'kode_pos' => $request->kode_pos,
                'no_telepon' => $request->no_telepon
            ],
            'shipping_method' => $request->shipping_method,
            'shipping_cost' => $request->shipping_cost,
            'items' => $cartItems,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ];

        $orders = Session::get('orders', []);
        $orders[] = $order;
        Session::put('orders', $orders);

        

        // --- Mutasi ke logistic order service via GraphQL ---
        try {
            $logisticGraphqlUrl = env('LOGISTIC_ORDER_GRAPHQL_URL', 'http://localhost:5112/graphql');
            $itemsJson = addslashes(json_encode(array_map(function($item) {
                return [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['harga']
                ];
            }, $order['items']), JSON_UNESCAPED_UNICODE));
            $shippingAddress = addslashes(json_encode($order['shipping_address'], JSON_UNESCAPED_UNICODE));

            $mutation = <<<GQL
            mutation {
              createOrder(
                userId: {$order['user_id']},
                shippingAddress: "{$shippingAddress}",
                totalAmount: {$order['total_amount']},
                items: "{$itemsJson}"
              ) {
                id
                userId
                shippingAddress
                totalAmount
                status
                createdAt
              }
            }
            GQL;

            $logisticResponse = Http::post($logisticGraphqlUrl, [
                'query' => $mutation
            ]);
            if (!$logisticResponse->successful()) {
                \Log::error('Logistic Order Sync Error', [
                    'status' => $logisticResponse->status(),
                    'body' => $logisticResponse->body()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Logistic Order Sync Exception', ['error' => $e->getMessage()]);
        }


        // Clear the cart
        Session::forget('cart');
        return redirect()->route('orders.success')
            ->with('order', $order)
            ->with('order_id', $order['id']);
    }

    private function encodeItems($items)
    {
        return addslashes(json_encode($items, JSON_UNESCAPED_UNICODE));
    }
}