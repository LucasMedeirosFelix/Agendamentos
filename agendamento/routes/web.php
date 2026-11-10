<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AppointmentController::class, 'create'])->name('booking');
Route::post('/agendamentos', [AppointmentController::class, 'store'])->name('appointments.store');
Route::get('/painel', [AppointmentController::class, 'index'])->name('dashboard');
Route::patch('/painel/agendamentos/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
Route::get('/painel/servicos', [ServiceController::class, 'index'])->name('services.index');
Route::post('/painel/servicos', [ServiceController::class, 'store'])->name('services.store');
Route::put('/painel/servicos/{service}', [ServiceController::class, 'update'])->name('services.update');
