@extends('layouts.app')

@section('title', $product['nama'])

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $product['nama'] }}</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <img src="{{ $product['gambar'] ?? 'https://via.placeholder.com/600x400' }}" 
                 class="card-img-top" 
                 alt="{{ $product['nama'] }}">
        </div>
    </div>
    
    <div class="col-md-6">
        <h1>{{ $product['nama'] }}</h1>
        
        <div class="mb-4">
            <h3>Rp {{ number_format($product['harga'], 0, ',', '.') }}</h3>
        </div>

        <div class="mb-4">
            <h5>Deskripsi</h5>
            <p>{{ $product['deskripsi'] }}</p>
        </div>

        @if($product['stok'] > 0)
        <form id="addToCartForm" class="mb-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <label for="quantity" class="form-label">Jumlah:</label>
                </div>
                <div class="col-auto">
                    <input type="number" 
                           class="form-control" 
                           id="quantity" 
                           name="quantity" 
                           value="1" 
                           min="1" 
                           max="{{ $product['stok'] }}">
                </div>
                <div class="col-auto">
                    <span class="text-success">
                        <i class="fas fa-check-circle"></i> Stok Tersedia ({{ $product['stok'] }})
                    </span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg mt-3">
                <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
            </button>
        </form>
        @else
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i> Stok Habis
        </div>
        @endif

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Detail Produk</h5>
                <table class="table table-borderless">
                    <tr>
                        <td>Kategori ID:</td>
                        <td>{{ $product['kategori_id'] }}</td>
                    </tr>
                    <tr>
                        <td>Stok:</td>
                        <td>{{ $product['stok'] }}</td>
                    </tr>
                    <tr>
                        <td>Berat:</td>
                        <td>{{ $product['weight'] ?? '-' }} kg</td>
                    </tr>
                    <tr>
                        <td>Kota Asal:</td>
                        <td>{{ $product['origin_city_id'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Terakhir Diperbarui:</td>
                        <td>{{ \Carbon\Carbon::parse($product['updated_at'])->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addToCartForm = document.getElementById('addToCartForm');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const quantity = document.getElementById('quantity').value;
            const token = document.querySelector('meta[name="csrf-token"]').content;
            
            fetch('/cart/add/{{ $product["id"] }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    quantity: parseInt(quantity)
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Produk berhasil ditambahkan ke keranjang!');
                    // Optional: redirect ke halaman cart
                    // window.location.href = '/cart';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error menambahkan produk ke keranjang');
            });
        });
    }
});
</script>
@endsection