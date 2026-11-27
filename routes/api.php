<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DriverController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::apiResource('drivers', DriverController::class)->names('api.drivers');
        Route::get('drivers/{driver}/documents/{document}', [DriverController::class, 'document'])
            ->name('api.drivers.documents.download');

        Route::apiResource('users', UserController::class)
            ->except('show')
            ->middleware('role:admin');
    });
});
