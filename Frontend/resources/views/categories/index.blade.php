@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Categories</h1>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @foreach($categories as $category)
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $category['name'] }}</h5>
                        @if(isset($category['description']))
                            <p class="card-text">{{ $category['description'] }}</p>
                        @endif
                        <a href="{{ route('categories.show', $category['id']) }}" class="btn btn-primary">
                            View Products
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(empty($categories))
        <div class="alert alert-info mt-4">
            No categories found.
        </div>
    @endif
</div>
@endsection 