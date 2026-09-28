<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Bisa diakses siapa saja, termasuk yang belum login
Route::get('/items', [ItemController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/items', [ItemController::class, 'store']);
    Route::patch('/items/{item}', [ItemController::class, 'markResolved']);
    Route::delete('/items/{item}', [ItemController::class, 'destroy']);
});