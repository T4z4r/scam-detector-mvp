<?php

use App\Services\SpamDetector;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/scam-check', function () {
    return view('scam-check');
});

Route::post('/scam-check', function (Request $request) {
    try {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000',
            'sender' => 'string|nullable|max:50',
        ]);

        if ($validator->fails()) {
            return view('scam-check', ['error' => $validator->errors()->first()]);
        }

        $detector = new SpamDetector();
        $result = $detector->predict($request->text, $request->sender ?? '');
        return view('scam-check', ['result' => $result]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Web route prediction error: ' . $e->getMessage());
        return view('scam-check', ['error' => 'Service temporarily unavailable. Please try again later.']);
    }
});

// Training routes
Route::get('/training', [TrainingController::class, 'index']);
Route::post('/training/train', [TrainingController::class, 'train']);
Route::get('/training/status', [TrainingController::class, 'status']);
Route::get('/training/data', [TrainingController::class, 'data']);
Route::post('/training/upload', [TrainingController::class, 'uploadData']);
Route::delete('/training/delete-data', [TrainingController::class, 'deleteData']);
Route::get('/training/metrics', [TrainingController::class, 'metrics']);
