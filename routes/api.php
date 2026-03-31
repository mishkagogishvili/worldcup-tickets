<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MatchesController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/matches', [MatchesController::class, 'store']);
    Route::get('/matches/{id}/report', [MatchesController::class, 'report']);
});

Route::get('/matches', [MatchesController::class, 'index']);
Route::get('/matches/{id}', [MatchesController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::post('/reservations/{id}/pay', [ReservationController::class, 'pay']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'cancel']);
});
