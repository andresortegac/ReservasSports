<?php

use App\Http\Controllers\Api\ExternalReservationController;
use Illuminate\Support\Facades\Route;

Route::post('/integraciones/reservas-externas', [ExternalReservationController::class, 'store']);
