<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => 'api','prefix' => 'auth'], function ($router) {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

});

// CRUD Products
Route::apiResource('products', ProductController::class);

// CART
Route::post('/cart', [CartController::class, 'store'])->middleware('auth:api');

// POS
Route::get('/pos', [POSController::class, 'index'])->middleware('auth:api');
Route::get('/pos/{id}', [POSController::class, 'show'])->middleware('auth:api');
Route::delete('/pos/{id}', [POSController::class, 'delete'])->middleware('auth:api');
