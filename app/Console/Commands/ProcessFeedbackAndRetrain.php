<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserFeedback;
use App\Models\TrainingData;
use App\Models\ScamSender;
use App\Services\SpamDetector;
use Illuminate\Support\Facades\Log;

class ProcessFeedbackAndRetrain extends Command
{
    protected $signature = 'scam:process-feedback {--auto-retrain : Automatically retrain model after processing}';
    protected $description = 'Process user feedback and optionally retrain the scam detection model';

    public function handle()
    {
        $this->info('Starting feedback processing...');

        $processedCount = 0;
        $retrainNeeded = false;

        try {
            // Process unprocessed feedback
            $unprocessedFeedback = UserFeedback::unprocessed()->get();

            $this->info("Found {$unprocessedFeedback->count()} unprocessed feedback items");

            foreach ($unprocessedFeedback as $feedback) {
                $this->processFeedbackItem($feedback);
                $processedCount++;
            }

            // Update scam sender confirmation status
            $this->updateScamSenderStatus();

            // Check if retraining is needed
            $newTrainingData = TrainingData::where('source', 'user_feedback')
                ->where('is_verified', false)
                ->where('created_at', '>=', now()->subDays(7)) // Last 7 days
                ->count();

            if ($newTrainingData >= 10) { // Need at least 10 new samples
                $retrainNeeded = true;
                $this->info("Found {$newTrainingData} new training samples. Retraining recommended.");
            }

            // Auto-retrain if requested and needed
            if ($this->option('auto-retrain') && $retrainNeeded) {
                $this->retrainModel();
            } elseif ($retrainNeeded) {
                $this->warn('New training data available. Run with --auto-retrain to retrain automatically.');
            }

            $this->info("Processed {$processedCount} feedback items successfully.");

            return 0;

        } catch (\Exception $e) {
            Log::error('Feedback processing error: ' . $e->getMessage());
            $this->error('Feedback processing failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Process individual feedback item
     */
    private function processFeedbackItem(UserFeedback $feedback)
    {
        switch ($feedback->feedback_type) {
            case UserFeedback::FEEDBACK_TYPE_SCAM_MESSAGE:
                $this->processScamMessageFeedback($feedback);
                break;

            case UserFeedback::FEEDBACK_TYPE_False_POSITIVE:
                $this->processFalsePositiveFeedback($feedback);
                break;

            case UserFeedback::FEEDBACK_TYPE_SCAM_SENDER:
                // Sender reports are already processed in the feedback controller
                break;

            default:
                $this->warn("Unknown feedback type: {$feedback->feedback_type}");
        }

        // Mark as processed
        $feedback->update(['is_processed' => true]);
    }

    /**
     * Process scam message feedback
     */
    private function processScamMessageFeedback(UserFeedback $feedback)
    {
        // Find corresponding training data and mark as verified
        $trainingData = TrainingData::where('text', $feedback->message_text)
            ->where('label', 'spam')
            ->where('source', 'user_feedback')
            ->first();

        if ($trainingData) {
            $trainingData->update([
                'is_verified' => true,
                'confidence_score' => 1.0
            ]);
            $this->line("Verified spam message: " . substr($feedback->message_text, 0, 50) . "...");
        } else {
            // Create new verified training sample
            TrainingData::create([
                'text' => $feedback->message_text,
                'label' => 'spam',
                'source' => 'user_feedback_verified',
                'is_verified' => true,
                'confidence_score' => 1.0
            ]);
            $this->line("Added new verified spam sample");
        }
    }

    /**
     * Process false positive feedback
     */
    private function processFalsePositiveFeedback(UserFeedback $feedback)
    {
        // Find corresponding training data and update label
        $trainingData = TrainingData::where('text', $feedback->message_text)
            ->where('label', 'spam')
            ->where('source', 'user_feedback')
            ->first();

        if ($trainingData) {
            $trainingData->update([
                'label' => 'ham',
                'is_verified' => true,
                'confidence_score' => 1.0
            ]);
            $this->line("Corrected false positive: " . substr($feedback->message_text, 0, 50) . "...");
        } else {
            // Create new verified ham sample
            TrainingData::create([
                'text' => $feedback->message_text,
                'label' => 'ham',
                'source' => 'user_feedback_verified',
                'is_verified' => true,
                'confidence_score' => 1.0
            ]);
            $this->line("Added new verified legitimate sample");
        }
    }

    /**
     * Update scam sender confirmation status
     */
    private function updateScamSenderStatus()
    {
        $scamSenders = ScamSender::where('is_confirmed', false)
            ->where('report_count', '>=', 5)
            ->get();

        foreach ($scamSenders as $sender) {
            $sender->update(['is_confirmed' => true]);
            $this->line("Confirmed scam sender: {$sender->sender_identifier} ({$sender->sender_type})");
        }
    }

    /**
     * Retrain the model with updated data
     */
    private function retrainModel()
    {
        $this->info('Starting model retraining...');

        try {
            $detector = new SpamDetector();
            $result = $detector->train();
            
            $this->info($result);
            $this->info('Model retrained successfully with latest feedback data.');

        } catch (\Exception $e) {
            Log::error('Auto-retrain failed: ' . $e->getMessage());
            $this->error('Model retraining failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
