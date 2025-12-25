<?php

use App\Services\SpamDetector;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/scam-check', function () {
    return view('scam-check');
});

Route::post('/scam-check', function (Request $request) {
    $detector = new SpamDetector();
    $result = $detector->predict($request->text ?? '', $request->sender ?? '');
    return view('scam-check', ['result' => $result]);
});
