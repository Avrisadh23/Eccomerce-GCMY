@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Our Products</h1>
        <div class="col-md-4">
            <form action="{{ route('products.index') }}" method="GET" class="d-flex">
                <input type="text" name="query" class="form-control me-2" placeholder="Search products..." value="{{ request('query') }}">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </form>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        @foreach($products as $product)
            <div class="col">
                <div class="card h-100">
                    @if($product['gambar'])
                        <img src="{{ $product['gambar'] }}" class="card-img-top" alt="{{ $product['nama'] }}">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $product['nama'] }}</h5>
                        <p class="card-text">{{ Str::limit($product['deskripsi'], 100) }}</p>
                        <p class="card-text">
                            <strong class="text-primary">Rp {{ number_format($product['harga'], 0, ',', '.') }}</strong>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">Stok: {{ $product['stok'] }}</small>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">Berat: {{ $product['weight'] ?? '-' }} kg</small>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">Kota Asal: {{ $product['origin_city_id'] ?? '-' }}</small>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('products.show', $product['id']) }}" class="btn btn-primary">View Details</a>
                            <form action="{{ route('cart.add', $product['id']) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary">
                                    Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(empty($products))
        <div class="alert alert-info mt-4">
            No products found.
        </div>
    @endif

    @if(isset($pagination) && $pagination['pages'] > 1)
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    @for($i = 1; $i <= $pagination['pages']; $i++)
                        <li class="page-item {{ $pagination['current_page'] == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ route('products.index', ['page' => $i]) }}">
                                {{ $i }}
                            </a>
                        </li>
                    @endfor
                </ul>
            </nav>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function(e) {
        // Implement search logic here
    });

    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const productId = this.dataset.productId;
            // Implement add to cart logic here
            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Product added to cart!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding product to cart');
            });
        });
    });
});
</script>
@endsection