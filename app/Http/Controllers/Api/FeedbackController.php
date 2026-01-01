<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserFeedback;
use App\Models\ScamSender;
use App\Models\TrainingData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    /**
     * Report a scam message
     */
    public function reportScamMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_text' => 'required|string|max:1000',
            'sender' => 'string|max:50|nullable',
            'original_prediction' => 'string|in:scam,safe,unknown|nullable',
            'original_confidence' => 'numeric|min:0|max:1|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $feedback = UserFeedback::create([
                'message_text' => $request->input('message_text'),
                'sender' => $request->input('sender'),
                'feedback_type' => UserFeedback::FEEDBACK_TYPE_SCAM_MESSAGE,
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'original_prediction' => $request->input('original_prediction'),
                'original_confidence' => $request->input('original_confidence')
            ]);

            // Also record the sender if provided
            if ($request->filled('sender')) {
                $this->recordScamSender($request->input('sender'), $request);
            }

            // Add to training data for future model improvement
            TrainingData::create([
                'text' => $request->input('message_text'),
                'label' => 'spam',
                'source' => 'user_feedback',
                'is_verified' => false, // Needs moderation
                'confidence_score' => 1.0 // User-confirmed as spam
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Scam message reported successfully. Thank you for helping improve our detection!',
                'data' => [
                    'feedback_id' => $feedback->id,
                    'message' => 'Your report helps train our AI model to better detect scams.'
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Feedback submission error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit feedback. Please try again later.'
            ], 500);
        }
    }

    /**
     * Report a scam sender/contact
     */
    public function reportScamSender(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_identifier' => 'required|string|max:100',
            'sender_type' => 'required|string|in:phone,email,name,short_code'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $senderIdentifier = $request->input('sender_identifier');
            $senderType = $request->input('sender_type');

            // Check if sender already exists
            $scamSender = ScamSender::where('sender_identifier', $senderIdentifier)
                ->where('sender_type', $senderType)
                ->first();

            if ($scamSender) {
                // Update existing record
                $scamSender->increment('report_count');
                $scamSender->update([
                    'last_reported_at' => now()
                ]);

                // Mark as confirmed if enough reports
                if ($scamSender->report_count >= 5 && !$scamSender->is_confirmed) {
                    $scamSender->update(['is_confirmed' => true]);
                }
            } else {
                // Create new record
                $scamSender = ScamSender::create([
                    'sender_identifier' => $senderIdentifier,
                    'sender_type' => $senderType,
                    'report_count' => 1,
                    'first_reported_at' => now(),
                    'last_reported_at' => now(),
                    'source' => 'user_report'
                ]);
            }

            // Also create user feedback record
            UserFeedback::create([
                'message_text' => null, // No specific message for sender reports
                'sender' => $senderIdentifier,
                'feedback_type' => UserFeedback::FEEDBACK_TYPE_SCAM_SENDER,
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_processed' => true
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Scam sender reported successfully. This information will help protect others.',
                'data' => [
                    'sender_identifier' => $senderIdentifier,
                    'sender_type' => $senderType,
                    'report_count' => $scamSender->report_count,
                    'is_confirmed' => $scamSender->is_confirmed
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Sender report error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit sender report. Please try again later.'
            ], 500);
        }
    }

    /**
     * Report false positive (legitimate message flagged as scam)
     */
    public function reportFalsePositive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_text' => 'required|string|max:1000',
            'sender' => 'string|max:50|nullable',
            'original_prediction' => 'required|string|in:scam',
            'original_confidence' => 'numeric|min:0|max:1|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $feedback = UserFeedback::create([
                'message_text' => $request->input('message_text'),
                'sender' => $request->input('sender'),
                'feedback_type' => UserFeedback::FEEDBACK_TYPE_False_POSITIVE,
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'original_prediction' => $request->input('original_prediction'),
                'original_confidence' => $request->input('original_confidence')
            ]);

            // Add to training data as legitimate message
            TrainingData::create([
                'text' => $request->input('message_text'),
                'label' => 'ham',
                'source' => 'user_feedback',
                'is_verified' => false, // Needs moderation
                'confidence_score' => 1.0 // User-confirmed as legitimate
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'False positive reported. This will help improve our model accuracy.',
                'data' => [
                    'feedback_id' => $feedback->id,
                    'message' => 'Thank you for the correction. This helps our AI learn.'
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('False positive report error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit false positive report.'
            ], 500);
        }
    }

    /**
     * Get feedback statistics
     */
    public function getFeedbackStats()
    {
        try {
            $stats = [
                'total_reports' => UserFeedback::count(),
                'scam_messages' => UserFeedback::scamMessages()->count(),
                'scam_senders' => UserFeedback::scamSenders()->count(),
                'false_positives' => UserFeedback::where('feedback_type', UserFeedback::FEEDBACK_TYPE_False_POSITIVE)->count(),
                'unprocessed' => UserFeedback::unprocessed()->count(),
                'known_scammers' => ScamSender::count(),
                'confirmed_scammers' => ScamSender::confirmed()->count(),
                'high_risk_senders' => ScamSender::highRisk()->count()
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Feedback statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Stats retrieval error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }

    /**
     * Record scam sender in database
     */
    private function recordScamSender(string $sender, Request $request)
    {
        if (empty($sender)) {
            return;
        }

        // Determine sender type
        $senderType = $this->getSenderType($sender);

        // Check if sender already exists
        $scamSender = ScamSender::where('sender_identifier', $sender)
            ->where('sender_type', $senderType)
            ->first();

        if ($scamSender) {
            $scamSender->increment('report_count');
            $scamSender->update(['last_reported_at' => now()]);
        } else {
            ScamSender::create([
                'sender_identifier' => $sender,
                'sender_type' => $senderType,
                'report_count' => 1,
                'first_reported_at' => now(),
                'last_reported_at' => now(),
                'source' => 'user_report'
            ]);
        }
    }

    /**
     * Determine sender type based on format
     */
    private function getSenderType(string $sender): string
    {
        // Phone number (10+ digits)
        if (preg_match('/^\d{10,}$/', $sender)) {
            return 'phone';
        }

        // Email address
        if (filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        // Short code (3-6 digits, commonly used for services)
        if (preg_match('/^\d{3,6}$/', $sender)) {
            return 'short_code';
        }

        // Default to name/identifier
        return 'name';
    }
}
