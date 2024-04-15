<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Reserva;
use App\Http\Controllers\ReservaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
    
});

Route::post('/storeReservas', [ReservaController::class, 'storeReservas']);
Route::get('/ListaReservas', [ReservaController::class, 'index']);
Route::put('/EditReservas/{id}', [ReservaController::class, 'EditReservas']);
Route::delete('/DeleteReserva/{id}', [ReservaController::class, 'destroy']);
Route::get('/reservas/dias-disponibles/{data_inicial}/{data_final}', [ReservaController::class, 'getDaysWithoutReservations']);
