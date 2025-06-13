<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderSuccessController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('register');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::group(['middleware' => 'web'], function () {
    Route::get('/home', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        return redirect()->route('products.index');
    })->name('home');
    
    Route::get('/profile', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        return app()->make(AuthController::class)->profile();
    })->name('profile');
    
    Route::put('/profile', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        return app()->make(AuthController::class)->updateProfile(request());
    })->name('profile.update');
    
    // Products
    Route::get('/products', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        return app()->make(ProductController::class)->index(request());
    })->name('products.index');
    
    Route::get('/products/{id}', function ($id) {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        return app()->make(ProductController::class)->show($id);
    })->name('products.show');
    
    // Categories
    Route::get('/categories', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        return app()->make(CategoryController::class)->index();
    })->name('categories.index');
    
    Route::get('/categories/{id}', function ($id) {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        return app()->make(CategoryController::class)->show($id);
    })->name('categories.show');
    
    // Cart
    Route::get('/cart', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return app()->make(CartController::class)->index();
    })->name('cart.index');
    
    Route::post('/cart/add/{productId}', function ($productId) {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return app()->make(CartController::class)->add($productId, request());
    })->name('cart.add');
    
    Route::post('/cart/remove/{productId}', function ($productId) {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return app()->make(CartController::class)->remove($productId);
    })->name('cart.remove');
    
    Route::post('/cart/update/{productId}', function ($productId) {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return app()->make(CartController::class)->update($productId, request());
    })->name('cart.update');
    
    Route::post('/cart/clear', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return app()->make(CartController::class)->clear();
    })->name('cart.clear');

    // Checkout Routes
    Route::get('/checkout', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return app()->make(CheckoutController::class)->index();
    })->name('checkout.index');

    Route::post('/checkout/calculate-shipping', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return app()->make(CheckoutController::class)->calculateShipping(request());
    })->name('checkout.calculate-shipping');

    Route::post('/checkout/process', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return app()->make(CheckoutController::class)->process(request());
    })->name('checkout.process');

    // Orders
    Route::get('/orders/success', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return app()->make(OrderSuccessController::class)->show();
    })->name('orders.success');

    Route::get('/orders', function () {
        if (!Session::has('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return app()->make(OrderController::class)->index();
    })->name('orders.index');
});
