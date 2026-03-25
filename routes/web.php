<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CanchaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ReservaExternaController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')
    ->controller(AuthenticatedSessionController::class)
    ->group(function () {
        Route::get('/', 'create')->name('login');
        Route::redirect('/login', '/');
        Route::post('/login', 'store')->name('login.store');
    });

Route::middleware('auth')->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/home', 'index')->name('dashboard');
    });

    Route::controller(AuthenticatedSessionController::class)->group(function () {
        Route::post('/logout', 'destroy')->name('logout');
    });

    Route::prefix('reservas')->name('reservas.')->group(function () {
        Route::get('horas-disponibles', [ReservaController::class, 'horasDisponibles'])
            ->name('horas-disponibles');
    });

    Route::resource('reservas', ReservaController::class);
    Route::resource('canchas', CanchaController::class)->except('show');

    Route::prefix('reservas-externas')->name('reservas.externas.')->group(function () {
        Route::get('/', [ReservaExternaController::class, 'index'])->name('index');
        Route::get('/create', [ReservaExternaController::class, 'create'])->name('create');
        Route::post('/', [ReservaExternaController::class, 'store'])->name('store');
        Route::get('/{reservas_externa}', [ReservaExternaController::class, 'show'])->name('show');
        Route::get('/{reservas_externa}/edit', [ReservaExternaController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{reservas_externa}', [ReservaExternaController::class, 'update'])->name('update');
        Route::delete('/{reservas_externa}', [ReservaExternaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('clientes')->name('clientes.')->controller(ClienteController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{cliente}/edit', 'edit')->name('edit');
        Route::match(['put', 'patch'], '/{cliente}', 'update')->name('update');
        Route::delete('/{cliente}', 'destroy')->name('destroy');
    });

    Route::prefix('ventas')->name('ventas.')->controller(VentaController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export', 'export')->name('export');
    });
});
