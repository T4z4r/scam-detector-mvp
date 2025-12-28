<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScamController;
use App\Http\Controllers\TrainingController;

// Scam detection endpoints
Route::post('/scam/check', [ScamController::class, 'check']);

// Training endpoints
Route::prefix('training')->group(function () {
    Route::post('/train', [TrainingController::class, 'train']);
    Route::get('/status', [TrainingController::class, 'status']);
    Route::get('/data', [TrainingController::class, 'data']);
    Route::post('/upload', [TrainingController::class, 'uploadData']);
    Route::delete('/data', [TrainingController::class, 'deleteData']);
    Route::get('/metrics', [TrainingController::class, 'metrics']);
});
