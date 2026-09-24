<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function (): void {
    Route::post('/register', 'register')->middleware('throttle:auth-register')->name('v1.auth.register');
    Route::get('/email/verify/{id}/{hash}', 'verifyEmail')
        ->name('verification.verify')
        ->middleware(['signed', 'throttle:6,1']);
    Route::post('/login', 'login')->middleware('throttle:auth-login')->name('v1.auth.login');
});
Route::middleware(['auth:sanctum', 'throttle:authorized'])->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me'])
        ->middleware('abilities:auth:me')
        ->name('v1.auth.me');
    Route::post('/wallets', [WalletController::class, 'store'])->middleware('abilities:wallets:store', 'verified')->name('v1.wallets.store');
    Route::post('/wallets/deposit', [WalletController::class, 'deposit'])->middleware('abilities:wallets:deposit', 'verified')->name('v1.wallets.deposit');
    Route::post('/wallets/transfer', [WalletController::class, 'transfer'])->middleware('abilities:wallets:transfer', 'verified')->name('v1.wallets.transfer');
    Route::get('/wallets/my', [WalletController::class, 'showMy'])->middleware('abilities:wallets:show:my', 'verified')->name('v1.wallets.show.my');
    Route::get('/wallets/transactions', [WalletController::class, 'index'])->middleware('abilities:wallets:transactions:index', 'verified')->name('v1.wallets.transactions.index');
    Route::post('/wallets/transactions/{transaction_id}/revert', [WalletController::class, 'revert'])->middleware('abilities:wallets:transactions:revert', 'verified')->name('v1.wallets.transaction.revert');
});
