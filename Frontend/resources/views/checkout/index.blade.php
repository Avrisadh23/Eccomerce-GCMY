@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-4">Data Pengiriman</h4>
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form id="checkoutForm" action="{{ route('checkout.process') }}" method="POST">
                        @csrf
                        
                        <!-- Data Penerima -->
                        <div class="mb-4">
                            <h5>Data Penerima</h5>
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" 
                                       id="nama_lengkap" name="nama_lengkap" 
                                       value="{{ old('nama_lengkap', $user['first_name'] . ' ' . $user['last_name']) }}" required>
                                @error('nama_lengkap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="no_telepon" class="form-label">Nomor Telepon</label>
                                <input type="tel" class="form-control @error('no_telepon') is-invalid @enderror" 
                                       id="no_telepon" name="no_telepon" 
                                       value="{{ old('no_telepon') }}" required>
                                @error('no_telepon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Alamat Pengiriman -->
                        <div class="mb-4">
                            <h5>Alamat Pengiriman</h5>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                          id="alamat" name="alamat" rows="3" required>{{ old('alamat') }}</textarea>
                                @error('alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="kota" class="form-label">Kota</label>
                                <select class="form-select @error('kota') is-invalid @enderror" id="kota" name="kota" required>
                                    <option value="">Pilih Kota</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city['city_id'] }}">{{ $city['city_name'] }}</option>
                                    @endforeach
                                </select>
                                @error('kota')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="kode_pos" class="form-label">Kode Pos</label>
                                <input type="text" class="form-control @error('kode_pos') is-invalid @enderror" 
                                       id="kode_pos" name="kode_pos" 
                                       value="{{ old('kode_pos') }}" required>
                                @error('kode_pos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Metode Pengiriman -->
                        <div class="mb-4">
                            <h5>Metode Pengiriman</h5>
                            <div id="shippingMethods" class="mb-3">
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Menghitung ongkos kirim...</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="courier" class="form-label">Pilih Kurir</label>
                                <select class="form-select" id="courier" name="courier" required>
                                    <option value="">Pilih Kurir</option>
                                    <option value="jne">JNE</option>
                                    <option value="pos">POS</option>
                                    <option value="tiki">TIKI</option>
                                </select>
                            </div>
                        </div>

                        <input type="hidden" name="shipping_cost" id="shipping_cost" value="">
                        <input type="hidden" name="shipping_method" id="shipping_method" value="">
                        <input type="hidden" name="courier" id="courier_hidden" value="">
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitButton" disabled>
                                Proses Pesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Ringkasan Pesanan</h4>
                    
                    <div class="mb-4">
                        @foreach($cartItems as $item)
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ $item['nama'] }} × {{ $item['quantity'] }}</span>
                                <span>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ongkos Kirim</span>
                        <span id="shippingCostDisplay">-</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total</strong>
                        <strong id="totalDisplay">Rp {{ number_format($total, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkoutForm');
    const submitButton = document.getElementById('submitButton');
    const shippingMethodsContainer = document.getElementById('shippingMethods');
    const shippingCostDisplay = document.getElementById('shippingCostDisplay');
    const totalDisplay = document.getElementById('totalDisplay');
    const subtotal = {{ $total }};
    
    // Function to format currency
    function formatCurrency(amount) {
        return 'Rp ' + amount.toLocaleString('id-ID');
    }

    // Function to calculate shipping cost
    function calculateShipping() {
        const kota = document.getElementById('kota').value;
        const courier = document.getElementById('courier').value;
        if (!kota || !courier) return;

        // Show loading state
        shippingMethodsContainer.innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Menghitung ongkos kirim...</p>
            </div>
        `;

        // Calculate total weight
        const totalWeight = {{ collect($cartItems)->sum('weight') ?? 1000 }};

        fetch('/checkout/calculate-shipping', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                destination: kota,
                weight: totalWeight,
                courier: courier
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                shippingMethodsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.error}
                    </div>
                `;
                return;
            }

            // Display shipping options
            let html = '<div class="list-group">';
            data.shipping_options.forEach((option, index) => {
                html += `
                    <label class="list-group-item">
                        <input class="form-check-input me-1" type="radio" name="shipping_option" 
                               value="${option.service}"
                               data-cost="${option.cost}"
                               ${index === 0 ? 'checked' : ''}>
                        ${option.service} - ${formatCurrency(option.cost)}
                        <small class="d-block text-muted">Estimasi ${option.etd} hari</small>
                    </label>
                `;
            });
            html += '</div>';
            
            shippingMethodsContainer.innerHTML = html;

            // Select first option by default
            const firstOption = document.querySelector('input[name="shipping_option"]');
            if (firstOption) {
                updateTotalWithShipping(firstOption.dataset.cost);
            }

            // Add event listeners to shipping options
            document.querySelectorAll('input[name="shipping_option"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    updateTotalWithShipping(this.dataset.cost);
                });
            });

            submitButton.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            shippingMethodsContainer.innerHTML = `
                <div class="alert alert-danger">
                    Gagal menghitung ongkos kirim. Silakan coba lagi.
                </div>
            `;
        });
    }

    // Function to update total with shipping cost
    function updateTotalWithShipping(shippingCost) {
        const cost = parseInt(shippingCost);
        const total = subtotal + cost;
        
        document.getElementById('shipping_cost').value = cost;
        document.getElementById('shipping_method').value = document.querySelector('input[name="shipping_option"]:checked').value;
        
        shippingCostDisplay.textContent = formatCurrency(cost);
        totalDisplay.textContent = formatCurrency(total);
    }

    // Calculate shipping when city or courier changes
    document.getElementById('kota').addEventListener('change', calculateShipping);
    document.getElementById('courier').addEventListener('change', calculateShipping);

    // Form validation
    form.addEventListener('submit', function(e) {
        if (!document.getElementById('shipping_method').value) {
            e.preventDefault();
            alert('Silakan pilih metode pengiriman');
        }
    });

    // Set value saat courier dipilih
    document.getElementById('courier').addEventListener('change', function() {
        document.getElementById('courier_hidden').value = this.value;
    });
});
</script>
@endsection