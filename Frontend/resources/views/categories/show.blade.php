@extends('layouts.app')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categories</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $category['name'] }}</li>
        </ol>
    </nav>

    <h1 class="mb-4">{{ $category['name'] }}</h1>
    @if(isset($category['description']))
        <p class="lead mb-4">{{ $category['description'] }}</p>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        @foreach($products['data'] as $product)
            <div class="col">
                <div class="card h-100">
                    @if(isset($product['gambar']))
                        <img src="{{ $product['gambar'] }}" class="card-img-top" alt="{{ $product['nama'] }}">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $product['nama'] }}</h5>
                        <p class="card-text">{{ Str::limit($product['deskripsi'], 100) }}</p>
                        <p class="card-text">
                            <strong>Harga: Rp {{ number_format($product['harga'], 0, ',', '.') }}</strong>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">Stok: {{ $product['stok'] }}</small>
                        </p>
                        <a href="{{ route('products.show', $product['id']) }}" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(empty($products['data']))
        <div class="alert alert-info mt-4">
            No products found in this category.
        </div>
    @endif

    @if(isset($products['pages']) && $products['pages'] > 1)
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    @for($i = 1; $i <= $products['pages']; $i++)
                        <li class="page-item {{ $products['current_page'] == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ route('categories.show', ['id' => $category['id'], 'page' => $i]) }}">
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