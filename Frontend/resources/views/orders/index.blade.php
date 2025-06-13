@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Pesanan Saya</h1>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(empty($orders))
        <div class="alert alert-info">
            <p class="mb-0">Anda belum memiliki pesanan.</p>
        </div>
    @else
        <div class="row">
            @foreach($orders as $order)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Order #{{ $order['id'] }}</h5>
                                <span class="badge bg-{{ $order['status'] === 'completed' ? 'success' : 'primary' }}">
                                    {{ ucfirst($order['status']) }}
                                </span>
                            </div>

                            <p class="text-muted mb-2">
                                <i class="fas fa-calendar"></i> 
                                {{ \Carbon\Carbon::parse($order['created_at'])->format('d M Y H:i') }}
                            </p>

                            <div class="mb-3">
                                <p class="mb-1"><strong>Pengiriman ke:</strong></p>
                                <p class="mb-0">{{ $order['shipping_address']['nama_lengkap'] }}</p>
                                <p class="mb-0">{{ $order['shipping_address']['alamat'] }}</p>
                                <p class="mb-0">{{ $order['shipping_address']['kota'] }} {{ $order['shipping_address']['kode_pos'] }}</p>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Total:</strong>
                                    <span class="ms-2">Rp {{ number_format($order['total_amount'], 0, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('orders.show', $order['id']) }}" class="btn btn-primary btn-sm">
                                    Detail Pesanan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection 