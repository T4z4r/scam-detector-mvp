<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScamController;

Route::post('/scam/check', [ScamController::class, 'check']);
