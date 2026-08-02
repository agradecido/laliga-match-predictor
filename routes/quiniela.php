<?php

use App\Http\Controllers\Quiniela\LeaderboardController;
use App\Http\Controllers\Quiniela\PredictionController;
use App\Http\Controllers\Quiniela\RoundController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/quiniela', [RoundController::class, 'index'])->name('quiniela.index');
    Route::get('/quiniela/{round}', [RoundController::class, 'show'])->name('quiniela.show');
    Route::post('/quiniela/{round}/predictions', [PredictionController::class, 'store'])->name('quiniela.predictions.store');

    Route::get('/quiniela-leaderboard', [LeaderboardController::class, 'index'])->name('quiniela.leaderboard');
});
