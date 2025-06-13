@extends('layouts.app')

@section('title', 'Order Success')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h2 class="mb-4">Terima Kasih!</h2>
                    <p class="mb-4">Pesanan Anda telah berhasil dibuat.</p>

                    @if(session('order'))
                        <div class="alert alert-info">
                            <p class="mb-0">Nomor Pesanan: <strong>{{ session('order')['id'] }}</strong></p>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Detail Pengiriman</h5>
                                <p class="mb-1"><strong>Nama:</strong> {{ session('order')['shipping_address']['nama_lengkap'] }}</p>
                                <p class="mb-1"><strong>Alamat:</strong> {{ session('order')['shipping_address']['alamat'] }}</p>
                                <p class="mb-1"><strong>Kota:</strong> {{ session('order')['shipping_address']['kota'] }}</p>
                                <p class="mb-1"><strong>Kode Pos:</strong> {{ session('order')['shipping_address']['kode_pos'] }}</p>
                                <p class="mb-0"><strong>No. Telepon:</strong> {{ session('order')['shipping_address']['no_telepon'] }}</p>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Ringkasan Pembayaran</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal Produk</span>
                                    <span>Rp {{ number_format(session('order')['total_amount'] - session('order')['shipping_cost'], 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Ongkos Kirim</span>
                                    <span>Rp {{ number_format(session('order')['shipping_cost'], 0, ',', '.') }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total</strong>
                                    <strong>Rp {{ number_format(session('order')['total_amount'], 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        <a href="{{ route('orders.index') }}" class="btn btn-primary">
                            Lihat Pesanan Saya
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                            Lanjut Belanja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 