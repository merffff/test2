<?php

//use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});





use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AddressController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Address routes
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses/search', [AddressController::class, 'search']);
    Route::post('/addresses', [AddressController::class, 'store']);
});

// In routes/api.php
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
Route::post('/login', function () {
    return response()->json(['message' => 'Маршрут входа работает!']);
});

Route::get('/test', function () {
    return response()->json(['message' => 'API работает!']);
});

