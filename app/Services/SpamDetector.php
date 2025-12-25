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
        // Don't load model in constructor to avoid exception during training
        $this->loadModel();
    }

    protected function loadModel()
    {
        $path = storage_path('app/' . $this->modelPath);
        if (file_exists($path)) {
            $this->pipeline = unserialize(file_get_contents($path));
        }else {
            throw new \Exception('Model not trained. Run "php artisan train:spam-model" first.');
        }
    }

    public function train(): string
    {
        try {
            // Load dataset (tab-separated: label\ttext)
            $datasetPath = storage_path('app/spam.csv');
            if (!file_exists($datasetPath)) {
                throw new \Exception('Dataset not found at ' . $datasetPath);
            }

            $dataset = new CsvDataset($datasetPath, 1, true, "\t");

            $samples = [];
            $targets = [];
            foreach ($dataset->getSamples() as $index => $sample) {
                $samples[] = $this->preprocess($sample[0]);
                $targets[] = $dataset->getTargets()[$index]; // 'spam' or 'ham'
            }

            if (empty($samples)) {
                throw new \Exception('No samples found in dataset');
            }

            $pipeline = new Pipeline(
                [new TokenCountVectorizer(new WordTokenizer()), new TfIdfTransformer()],
                new NaiveBayes()
            );

            $pipeline->train($samples, $targets);

            // Save the model
            file_put_contents(storage_path('app/' . $this->modelPath), serialize($pipeline));
            $this->pipeline = $pipeline;

            Log::info('Spam model trained successfully with ' . count($samples) . ' samples.');
            return 'Model trained and saved successfully!';
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
        // Check if model exists
        if (!$this->pipeline) {
            try {
                $this->loadModel();
                if (!$this->pipeline) {
                    return [
                        'label' => 'unknown',
                        'confidence' => 0,
                        'reason' => 'Model not trained. Please run training first.'
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'label' => 'unknown',
                    'confidence' => 0,
                    'reason' => 'Model not available: ' . $e->getMessage()
                ];
            }
        }

        $processed = $this->preprocess($text . ' ' . $sender);

        // Rule-based override for high-confidence TZ/KE scams
        $lowerInput = strtolower($text . ' ' . $sender);
        if (preg_match('/(mpesa reversal|flex loan|confirm pin|godi|http|tsh \d{4,}|thibitisha pin|pesa imerudiwa)/i', $lowerInput)) {
            return [
                'label' => 'scam',
                'confidence' => 0.99,
                'reason' => 'Matches TZ/KE scam patterns (e.g., M-Pesa reversal)'
            ];
        }

        try {
            $prediction = $this->pipeline->predict([$processed])[0];
            $classifier = $this->pipeline->getClassifier();

            // Get probabilities from the classifier
            $probabilities = $classifier->predictProbability([$processed])[0];

            $label = ($prediction === 'spam') ? 'scam' : 'safe';
            $confidence = max($probabilities['spam'] ?? 0, $probabilities['ham'] ?? 0);

            Log::info('Prediction made: ' . $label . ' (confidence: ' . $confidence . ')');

            return [
                'label' => $label,
                'confidence' => $confidence,
                'reason' => 'ML classification'
            ];
        } catch (\Exception $e) {
            Log::error('Prediction error: ' . $e->getMessage());
            return [
                'label' => 'unknown',
                'confidence' => 0,
                'reason' => 'Prediction failed: ' . $e->getMessage()
            ];
        }
    }

    public function isModelTrained(): bool
    {
        return $this->pipeline !== null;
    }
}
