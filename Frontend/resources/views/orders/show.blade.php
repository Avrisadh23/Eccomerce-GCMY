@extends('layouts.app')

@section('title', 'Order Detail')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Detail Pesanan #{{ $order['id'] }}</h1>
                <span class="badge bg-{{ $order['status'] === 'completed' ? 'success' : 'primary' }} fs-5">
                    {{ ucfirst($order['status']) }}
                </span>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Informasi Pengiriman -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informasi Pengiriman</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Nama Penerima:</strong></p>
                            <p class="mb-3">{{ $order['shipping_address']['nama_lengkap'] }}</p>

                            <p class="mb-1"><strong>Nomor Telepon:</strong></p>
                            <p class="mb-3">{{ $order['shipping_address']['no_telepon'] }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Alamat Pengiriman:</strong></p>
                            <p class="mb-1">{{ $order['shipping_address']['alamat'] }}</p>
                            <p class="mb-1">{{ $order['shipping_address']['kota'] }}</p>
                            <p class="mb-3">{{ $order['shipping_address']['kode_pos'] }}</p>
                        </div>
                    </div>
                    <div class="mt-2">
                        <p class="mb-1"><strong>Metode Pengiriman:</strong></p>
                        <p class="mb-0">{{ $order['shipping_method'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Detail Produk -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Detail Produk</h5>
                    
                    @foreach($order['items'] as $item)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">{{ $item['nama'] }}</h6>
                                <p class="text-muted mb-0">{{ $item['quantity'] }} × Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                            </div>
                            <span>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Ringkasan Pembayaran -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Ringkasan Pembayaran</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal Produk</span>
                        <span>Rp {{ number_format($order['total_amount'] - $order['shipping_cost'], 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ongkos Kirim</span>
                        <span>Rp {{ number_format($order['shipping_cost'], 0, ',', '.') }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <strong>Total</strong>
                        <strong>Rp {{ number_format($order['total_amount'], 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 