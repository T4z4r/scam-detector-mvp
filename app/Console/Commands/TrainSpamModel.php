<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SpamDetector;

class TrainSpamModel extends Command
{
    protected $signature = 'train:spam-model';
    protected $description = 'Train the spam/scam detection model using the dataset';

    public function handle()
    {
        try {
            $this->info('Starting model training...');
            $detector = new SpamDetector();
            $result = $detector->train();
            $this->info($result);
        } catch (\Exception $e) {
            $this->error('Training failed: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Training error: ' . $e->getMessage());
            return 1;
        }
        return 0;
    }
}
