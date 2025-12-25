<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SpamDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ScamController extends Controller
{
    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000',
            'sender' => 'string|nullable|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $detector = new SpamDetector();
            $result = $detector->predict($request->input('text'), $request->input('sender', ''));

            return response()->json([
                'result' => $result['label'],
                'confidence' => round($result['confidence'] * 100, 2) . '%',
                'reason' => $result['reason'],
                'alert' => ($result['label'] === 'scam') ? '🚨 SCAM DETECTED! Ignore, block sender, and report to authorities (e.g., 333 in TZ/KE).' : '✅ Appears safe.'
            ]);
        } catch (\Exception $e) {
            Log::error('Prediction error: ' . $e->getMessage());
            return response()->json(['error' => 'Service unavailable. Please try again later.'], 500);
        }
    }
}
