<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function (): void {
    Route::post('/register', 'register')->middleware('throttle:auth-register')->name('v1.auth.register');
    Route::get('/email/verify/{id}/{hash}', 'verifyEmail')
        ->name('verification.verify')
        ->middleware(['signed', 'throttle:6,1']);
    Route::post('/login', 'login')->middleware('throttle:auth-login')->name('v1.auth.login');
});
