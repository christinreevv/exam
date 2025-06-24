<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/cart/add/{product}', [CartController::class, 'add']);
//     Route::get('/cart', [CartController::class, 'show']);

// });

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
 Route::get('/categories', [CategoryController::class, 'index']);
 Route::get('/products/latest', [ProductController::class, 'latest']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/cart/add/{product}', [CartController::class, 'add']);
    Route::get('/cart', [CartController::class, 'show']);
    Route::patch('/cart/item/{item}', [CartController::class, 'updateQuantity']);
    Route::post('/order', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::delete('orders/{order}', [OrderController::class, 'destroy']);


    Route::middleware('admin')->group(function () {
        Route::get('/admin/products', [AdminController::class, 'getAllProducts']);
        Route::post('/admin/products', [AdminController::class, 'storeProduct']);
        Route::put('/admin/products/{product}', [AdminController::class, 'updateProduct']);
        Route::delete('/admin/products/{product}', [AdminController::class, 'deleteProduct']);

        Route::post('/admin/categories', [AdminController::class, 'createCategory']);
        Route::delete('/admin/categories/{category}', [AdminController::class, 'deleteCategory']);

        Route::patch('/admin/orders/{order}/confirm', [OrderController::class, 'confirm']);
        Route::patch('/admin/orders/{order}/cancel', [OrderController::class, 'cancel']);
    });

});


// Route::middleware('auth:sanctum')->get('/test-auth', function () {
//     return response()->json(auth()->user());
// });




