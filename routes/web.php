<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebAuthController::class, 'showLogin'])->name('login');

Route::post('/login', [WebAuthController::class, 'login'])
    ->middleware('guest')
    ->name('login.store');

Route::post('/logout', [WebAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/drivers/{driver}/documents/{document}', [DriverController::class, 'document'])
        ->name('drivers.documents.download');

    Route::resource('drivers', DriverController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});
