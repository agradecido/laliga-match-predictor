<?php

use App\Http\Controllers\Admin\PotController;
use App\Http\Controllers\Admin\PredictionsController;
use App\Http\Controllers\Admin\ResultsController;
use App\Http\Controllers\Admin\RoundController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/results', [ResultsController::class, 'index'])->name('results.index');
    Route::get('/results/{round}', [ResultsController::class, 'edit'])->name('results.edit');
    Route::put('/results/{round}', [ResultsController::class, 'update'])->name('results.update');

    Route::get('/rounds', [RoundController::class, 'index'])->name('rounds.index');
    Route::get('/rounds/create', [RoundController::class, 'create'])->name('rounds.create');
    Route::post('/rounds', [RoundController::class, 'store'])->name('rounds.store');
    Route::get('/rounds/{round}/edit', [RoundController::class, 'edit'])->name('rounds.edit');
    Route::put('/rounds/{round}', [RoundController::class, 'update'])->name('rounds.update');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/predictions', [PredictionsController::class, 'index'])->name('predictions.index');
    Route::get('/predictions/{round}', [PredictionsController::class, 'edit'])->name('predictions.edit');
    Route::put('/predictions/{round}', [PredictionsController::class, 'update'])->name('predictions.update');

    Route::get('/pot', [PotController::class, 'index'])->name('pot.index');
    Route::put('/pot/fee', [PotController::class, 'updateFee'])->name('pot.fee.update');
    Route::post('/pot/payments/{user}/toggle', [PotController::class, 'togglePayment'])->name('pot.payments.toggle');
    Route::post('/pot/payout', [PotController::class, 'payout'])->name('pot.payout');
});
