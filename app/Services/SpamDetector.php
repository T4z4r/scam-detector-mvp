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
        // Use direct path to avoid storage_path issues in CLI
        $storagePath = __DIR__ . '/../../storage/app/' . $this->modelPath;

        if (file_exists($storagePath)) {
            $this->pipeline = unserialize(file_get_contents($storagePath));
        }
        // Don't throw exception - allow training to proceed
    }

    protected function safeLog($level, $message)
    {
        if (class_exists('Illuminate\Support\Facades\Log')) {
            try {
                Log::$level($message);
            } catch (\Exception $e) {
                // Silently ignore logging errors
            }
        }
    }

    public function train(): string
    {
        try {
            // Load dataset (tab-separated: label\ttext)
            $datasetPath = __DIR__ . '/../../storage/app/spam.csv';
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
            $modelPath = __DIR__ . '/../../storage/app/' . $this->modelPath;
            file_put_contents($modelPath, serialize($pipeline));
            $this->pipeline = $pipeline;

            $this->safeLog('info', 'Spam model trained successfully with ' . count($samples) . ' samples.');
            return 'Model trained and saved successfully!';
        } catch (\Exception $e) {
            $this->safeLog('error', 'Training failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function preprocess(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        // TZ scam keywords as features (Tanzania focused)
        $text = preg_replace('/(mpesa|pesa|imeri|tigo airtel|halotel|godi|reversal|pin|loan|tsh|thibitisha)/i', ' SCAM_KEYWORD ', $text);
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

        // Rule-based override for high-confidence TZ scams (Tanzania focused)
        $lowerInput = strtolower($text . ' ' . $sender);
        if (preg_match('/(mpesa reversal|pesa imerudiwa|tigo money|airtel money|halotel|confirm pin|godi|http|tsh \d{4,}|thibitisha pin)/i', $lowerInput)) {
            return [
                'label' => 'scam',
                'confidence' => 0.99,
                'reason' => 'Matches TZ scam patterns (e.g., M-Pesa reversal, Tigo Money)'
            ];
        }

        try {
            $prediction = $this->pipeline->predict([$processed])[0];

            // For probability calculation, we need to use the estimator directly
            // Since Pipeline doesn't expose getClassifier(), we'll use a simpler approach
            $probabilities = [];
            try {
                // Try to get probabilities from the pipeline directly
                if (method_exists($this->pipeline, 'predictProbability')) {
                    $probabilities = $this->pipeline->predictProbability([$processed])[0];
                } else {
                    // Fallback: assume reasonable probabilities based on prediction
                    $probabilities = ($prediction === 'spam')
                        ? ['spam' => 0.8, 'ham' => 0.2]
                        : ['spam' => 0.2, 'ham' => 0.8];
                }
            } catch (\Exception $e) {
                // Fallback probabilities
                $probabilities = ($prediction === 'spam')
                    ? ['spam' => 0.8, 'ham' => 0.2]
                    : ['spam' => 0.2, 'ham' => 0.8];
            }

            $label = ($prediction === 'spam') ? 'scam' : 'safe';
            $confidence = max($probabilities['spam'] ?? 0, $probabilities['ham'] ?? 0);

            $this->safeLog('info', 'Prediction made: ' . $label . ' (confidence: ' . $confidence . ')');

            return [
                'label' => $label,
                'confidence' => $confidence,
                'reason' => 'ML classification'
            ];
        } catch (\Exception $e) {
            $this->safeLog('error', 'Prediction error: ' . $e->getMessage());
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

