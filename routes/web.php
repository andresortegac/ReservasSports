<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ReservaExternaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// BD PRINCIPAL (CRUD y crear reserva)
Route::resource('reservas', ReservaController::class);

// BD EXTERNA (solo ver)
Route::get('reservas-externas', [ReservaExternaController::class, 'index'])
    ->name('reservas.externas.index');

Route::resource('reservas-externas', ReservaExternaController::class)
    ->names('reservas.externas');

// CLIENTES + métricas
Route::resource('clientes', ClienteController::class)->except(['show']);

// VENTAS
Route::get('ventas', [VentaController::class, 'index'])->name('ventas.index');
Route::get('ventas/export', [VentaController::class, 'export'])->name('ventas.export'); // opcional

Route::get('reservas/horas-disponibles', [ReservaController::class, 'horasDisponibles'])
    ->name('reservas.horas-disponibles');