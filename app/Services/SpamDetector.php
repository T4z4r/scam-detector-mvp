<?php

namespace App\Services;

use Phpml\Classification\NaiveBayes;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\Pipeline;
use Phpml\Dataset\CsvDataset;
use Phpml\Tokenization\WordTokenizer;
use Illuminate\Support\Facades\Log;

class SpamDetector
{
    protected $pipeline;
    protected $modelPath = 'spam_model.phpml';

    public function __construct()
    {
        $this->loadModel();
    }

    protected function loadModel()
    {
        $path = storage_path('app/' . $this->modelPath);
        if (file_exists($path)) {
            $this->pipeline = unserialize(file_get_contents($path));
        } else {
            throw new \Exception('Model not trained. Run "php artisan train:spam-model" first.');
        }
    }

    public function train()
    {
        try {
            // Load dataset (tab-separated: label\ttext)
            $dataset = new CsvDataset(storage_path('app/spam.csv'), 1, true, "\t");

            $samples = [];
            $targets = [];
            foreach ($dataset->getSamples() as $index => $sample) {
                $samples[] = $this->preprocess($sample[0]);
                $targets[] = $dataset->getTargets()[$index]; // 'spam' or 'ham'
            }

            $pipeline = new Pipeline([
                new TokenCountVectorizer(new WordTokenizer()),
                new TfIdfTransformer(),
                new NaiveBayes()
            ]);

            $pipeline->train($samples, $targets);

            file_put_contents(storage_path('app/' . $this->modelPath), serialize($pipeline));
            $this->pipeline = $pipeline;

            Log::info('Spam model trained successfully.');
            return 'Model trained and saved!';
        } catch (\Exception $e) {
            Log::error('Training failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function preprocess(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        // TZ/KE scam keywords as features
        $text = preg_replace('/(mpesa|flex|pesa|godi|reversal|pin|loan|tsh|thibitisha)/i', ' SCAM_KEYWORD ', $text);
        $text = preg_replace('/http\S+|www\S+/i', ' SCAM_URL ', $text);
        $text = preg_replace('/\d{4,}/', ' SCAM_NUMBER ', $text); // Large amounts
        return trim($text);
    }

    public function predict(string $text, string $sender = ''): array
    {
        $processed = $this->preprocess($text . ' ' . $sender);

        // Rule-based override for high-confidence TZ/KE scams
        $lowerInput = strtolower($text . ' ' . $sender);
        if (preg_match('/(mpesa reversal|flex loan|confirm pin|godi|http|tsh \d{4,}|thibitisha pin|pesa imerudiwa)/i', $lowerInput)) {
            return ['label' => 'scam', 'confidence' => 0.99, 'reason' => 'Matches TZ/KE scam patterns (e.g., M-Pesa reversal)'];
        }

        $prediction = $this->pipeline->predict([$processed])[0];
        $probabilities = $this->pipeline->getClassifier()->predictProbability([$processed])[0];

        $label = ($prediction === 'spam') ? 'scam' : 'safe';
        $confidence = max($probabilities['spam'] ?? 0, $probabilities['ham'] ?? 0);

        Log::info('Prediction made: ' . $label . ' (confidence: ' . $confidence . ')');

        return [
            'label' => $label,
            'confidence' => $confidence,
            'reason' => 'ML classification'
        ];
    }
}
