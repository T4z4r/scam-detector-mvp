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
        // Convert to lowercase for consistency but preserve important structure
        $text = strtolower($text);
        
        // Enhanced feature extraction with more patterns
        $features = [];
        
        // Money transfer services (key scam indicators)
        if (preg_match('/(mpesa|pesa|tigo|airtel|halotel|godi)/i', $text)) {
            $features[] = 'money_service';
        }
        
        // Financial actions (suspicious patterns)
        if (preg_match('/(reversal|balance|statement|transaction)/i', $text)) {
            $features[] = 'financial_action';
        }
        
        // Authentication requests (major red flag)
        if (preg_match('/(pin|password|verify|confirm|enter)/i', $text)) {
            $features[] = 'auth_request';
        }
        
        // Urgent language (social engineering)
        if (preg_match('/(urgent|immediately|now|asap|emergency|suspended)/i', $text)) {
            $features[] = 'urgent_tone';
        }
        
        // Prize/lottery language (common scam)
        if (preg_match('/(win|won|winner|prize|congratulations|lottery)/i', $text)) {
            $features[] = 'prize_language';
        }
        
        // Money amounts (especially large ones)
        if (preg_match('/\d+.*(tsh|ksh|shillings?)/i', $text)) {
            $features[] = 'money_amount';
        }
        
        // Links and URLs (phishing indicator)
        if (preg_match('/(http|www|\.com|\.net|\.org|click|link)/i', $text)) {
            $features[] = 'suspicious_link';
        }
        
        // Phone numbers (contact harvesting)
        if (preg_match('/\d{10,}/', $text)) {
            $features[] = 'phone_number';
        }
        
        // Government/official language (impersonation)
        if (preg_match('/(government|bank|official|authority)/i', $text)) {
            $features[] = 'official_claim';
        }
        
        // Loan/credit offers
        if (preg_match('/(loan|credit|finance|investment)/i', $text)) {
            $features[] = 'financial_offer';
        }
        
        // Clean text more gently - keep more meaningful words
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Combine original cleaned text with extracted features
        $processedText = trim($text) . ' ' . implode(' ', $features);
        
        return $processedText;
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

        // Extract pattern features for better understanding (but don't override ML)
        $patternFeatures = $this->extractPatternFeatures($text . ' ' . $sender);
        
        try {
            $prediction = $this->pipeline->predict([$processed])[0];

            // Get probability scores for better confidence calculation
            $probabilities = [];
            try {
                // Try to get probabilities from the pipeline directly
                if (method_exists($this->pipeline, 'predictProbability')) {
                    $probabilities = $this->pipeline->predictProbability([$processed])[0];
                } else {
                    // Fallback: use model confidence for probability estimation
                    $confidenceScore = $this->estimateConfidence($processed, $prediction);
                    $probabilities = ($prediction === 'spam')
                        ? ['spam' => $confidenceScore, 'ham' => 1 - $confidenceScore]
                        : ['spam' => 1 - $confidenceScore, 'ham' => $confidenceScore];
                }
            } catch (\Exception $e) {
                // Fallback to pattern-based confidence if ML fails
                $confidenceScore = $this->calculatePatternConfidence($patternFeatures);
                $probabilities = ($prediction === 'spam')
                    ? ['spam' => $confidenceScore, 'ham' => 1 - $confidenceScore]
                    : ['spam' => 1 - $confidenceScore, 'ham' => $confidenceScore];
            }

            $label = ($prediction === 'spam') ? 'scam' : 'safe';
            $confidence = max($probabilities['spam'] ?? 0, $probabilities['ham'] ?? 0);
            
            // Enhance reason with pattern insights
            $reason = $this->generateReason($label, $confidence, $patternFeatures);

            $this->safeLog('info', 'ML Prediction made: ' . $label . ' (confidence: ' . $confidence . ')');

            return [
                'label' => $label,
                'confidence' => $confidence,
                'reason' => $reason
            ];
        } catch (\Exception $e) {
            $this->safeLog('error', 'Prediction error: ' . $e->getMessage());
            
            // Fallback to pattern-based classification if ML completely fails
            $patternLabel = $this->classifyByPatterns($patternFeatures);
            $patternConfidence = $this->calculatePatternConfidence($patternFeatures);
            
            return [
                'label' => $patternLabel,
                'confidence' => $patternConfidence,
                'reason' => 'Pattern-based classification (ML unavailable)'
            ];
        }
    }

    public function isModelTrained(): bool
    {
        return $this->pipeline !== null;
    }

    /**
     * Extract pattern features for analysis
     */
    protected function extractPatternFeatures(string $text): array
    {
        $features = [];
        $lowerText = strtolower($text);
        
        // Check for common scam patterns
        $features['has_money_transfer'] = preg_match('/(mpesa|tigo|airtel|halotel|godi)/i', $lowerText);
        $features['has_urgent_language'] = preg_match('/(urgent|immediately|now|asap|emergency)/i', $lowerText);
        $features['has_prize_language'] = preg_match('/(win|won|winner|prize|congratulations)/i', $lowerText);
        $features['has_info_request'] = preg_match('/(send|share|provide|enter).*(pin|account|number|details)/i', $lowerText);
        $features['has_url'] = preg_match('/http|www|\.com|\.net|\.org/', $lowerText);
        $features['has_large_amounts'] = preg_match('/\d{4,}/', $text);
        $features['has_phone_number'] = preg_match('/\d{10,}/', $text);
        $features['has_reversal'] = preg_match('/reversal|imerudiwa/i', $lowerText);
        $features['has_loan'] = preg_match('/loan|credit/i', $lowerText);
        
        return $features;
    }
    
    /**
     * Estimate confidence based on text features
     */
    protected function estimateConfidence(string $processedText, string $prediction): float
    {
        // Simple confidence estimation based on feature density
        $wordCount = str_word_count($processedText);
        $featureCount = substr_count($processedText, ' ');
        
        // Higher feature density suggests more confident prediction
        $density = $wordCount > 0 ? $featureCount / $wordCount : 0;
        
        // Base confidence on prediction type and feature density
        $baseConfidence = ($prediction === 'spam') ? 0.7 : 0.8;
        $densityBonus = min($density * 0.2, 0.15);
        
        return min($baseConfidence + $densityBonus, 0.95);
    }
    
    /**
     * Calculate confidence based on pattern features
     */
    protected function calculatePatternConfidence(array $features): float
    {
        $spamScore = 0;
        $hamScore = 0;
        
        // Weight different features
        if ($features['has_money_transfer']) $spamScore += 0.3;
        if ($features['has_urgent_language']) $spamScore += 0.2;
        if ($features['has_prize_language']) $spamScore += 0.25;
        if ($features['has_info_request']) $spamScore += 0.3;
        if ($features['has_url']) $spamScore += 0.15;
        if ($features['has_large_amounts']) $spamScore += 0.1;
        if ($features['has_reversal']) $spamScore += 0.2;
        if ($features['has_loan']) $spamScore += 0.15;
        
        // Normal Ham indicators
        if ($features['has_phone_number'] && !$features['has_urgent_language']) $hamScore += 0.1;
        
        $totalScore = $spamScore + $hamScore;
        return $totalScore > 0 ? min($spamScore / $totalScore, 0.9) : 0.5;
    }
    
    /**
     * Generate human-readable reason for classification
     */
    protected function generateReason(string $label, float $confidence, array $features): string
    {
        if ($label === 'scam') {
            $reasons = [];
            if ($features['has_money_transfer']) $reasons[] = 'money transfer service mentioned';
            if ($features['has_urgent_language']) $reasons[] = 'urgent language used';
            if ($features['has_prize_language']) $reasons[] = 'prize/lottery language';
            if ($features['has_info_request']) $reasons[] = 'requests sensitive information';
            if ($features['has_url']) $reasons[] = 'contains suspicious links';
            if ($features['has_reversal']) $reasons[] = 'mentions money reversal';
            if ($features['has_loan']) $reasons[] = 'financial service offers';
            
            if (empty($reasons)) {
                return 'ML classification - pattern analysis suggests scam';
            }
            
            return 'ML classification - detected: ' . implode(', ', $reasons);
        } else {
            return 'ML classification - normal communication patterns';
        }
    }
    
    /**
     * Classify based on patterns when ML is unavailable
     */
    protected function classifyByPatterns(array $features): string
    {
        $confidence = $this->calculatePatternConfidence($features);
        return $confidence > 0.6 ? 'scam' : 'safe';
    }
}

