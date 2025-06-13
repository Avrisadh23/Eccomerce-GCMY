@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Keranjang Belanja</h1>

            @if(count($cartItems) > 0)
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 100px">Gambar</th>
                                        <th>Produk</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-center" style="width: 150px">Jumlah</th>
                                        <th class="text-end">Subtotal</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cartItems as $item)
                                        <tr>
                                            <td>
                                                <img src="{{ $item['gambar'] ?? 'https://via.placeholder.com/100' }}" 
                                                     alt="{{ $item['nama'] }}"
                                                     class="img-thumbnail"
                                                     style="max-width: 100px">
                                            </td>
                                            <td>
                                                <h5 class="mb-1">{{ $item['nama'] }}</h5>
                                                <a href="{{ route('products.show', $item['id']) }}" class="text-muted">
                                                    Lihat Detail
                                                </a>
                                            </td>
                                            <td class="text-end">
                                                Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <button class="btn btn-outline-secondary btn-sm quantity-decrease" 
                                                            type="button"
                                                            data-product-id="{{ $item['id'] }}">
                                                        -
                                                    </button>
                                                    <input type="number" 
                                                           class="form-control text-center quantity-input" 
                                                           value="{{ $item['quantity'] }}"
                                                           min="1"
                                                           data-product-id="{{ $item['id'] }}"
                                                           style="width: 60px">
                                                    <button class="btn btn-outline-secondary btn-sm quantity-increase" 
                                                            type="button"
                                                            data-product-id="{{ $item['id'] }}">
                                                        +
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-sm remove-item"
                                                        data-product-id="{{ $item['id'] }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end">
                                            <strong>Total:</strong>
                                        </td>
                                        <td class="text-end">
                                            <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <div>
                        <button class="btn btn-danger" id="clear-cart">
                            <i class="fas fa-trash"></i> Kosongkan Keranjang
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Lanjut Belanja
                        </a>
                    </div>
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart"></i> Checkout
                    </a>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-shopping-cart"></i> Keranjang belanja Anda kosong.
                    <a href="{{ route('products.index') }}" class="alert-link">Lanjutkan Belanja</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update quantity dengan tombol + dan -
    const decreaseButtons = document.querySelectorAll('.quantity-decrease');
    const increaseButtons = document.querySelectorAll('.quantity-increase');
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const token = document.querySelector('meta[name="csrf-token"]').content;

    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            if (input.value > 1) {
                input.value = parseInt(input.value) - 1;
                updateQuantity(input);
            }
        });
    });

    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            input.value = parseInt(input.value) + 1;
            updateQuantity(input);
        });
    });

    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
            updateQuantity(this);
        });
    });

    function updateQuantity(input) {
        const productId = input.dataset.productId;
        const quantity = input.value;

        fetch(`/cart/update/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ quantity: parseInt(quantity) })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error mengubah jumlah produk');
        });
    }

    // Hapus item
    const removeButtons = document.querySelectorAll('.remove-item');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            if(confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
                const productId = this.dataset.productId;
                
                fetch(`/cart/remove/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error menghapus produk');
                });
            }
        });
    });

    // Kosongkan keranjang
    const clearCartButton = document.getElementById('clear-cart');
    if(clearCartButton) {
        clearCartButton.addEventListener('click', function() {
            if(confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
                fetch('/cart/clear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error mengosongkan keranjang');
                });
            }
        });
    }
});
</script>
@endsection 