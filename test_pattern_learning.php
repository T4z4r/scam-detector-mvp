<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$detector = new App\Services\SpamDetector();

echo "=== Testing Pattern Learning vs String Matching ===\\n\\n";

$testCases = [
    // Training data examples (should be recognized)
    [
        'text' => 'mpesa reversal ksh 2500 click http://bit.ly/xyz confirm pin now',
        'expected' => 'scam',
        'description' => 'Exact training example'
    ],
    [
        'text' => 'meeting at 3pm today please confirm attendance',
        'expected' => 'safe', 
        'description' => 'Exact training example'
    ],
    
    // Pattern variations (should be learned)
    [
        'text' => 'mpesa reversal for ksh 5000 pending confirm pin immediately',
        'expected' => 'scam',
        'description' => 'Similar pattern - different amount'
    ],
    [
        'text' => 'urgent meeting at 2pm please confirm attendance now',
        'expected' => 'safe',
        'description' => 'Similar pattern - legitimate message with urgent language'
    ],
    [
        'text' => 'tigo money reversal ksh 1000 click link to confirm',
        'expected' => 'scam',
        'description' => 'Pattern learning - different money service'
    ],
    [
        'text' => 'your appointment tomorrow at 9am is confirmed',
        'expected' => 'safe',
        'description' => 'Pattern learning - legitimate appointment'
    ],
    
    // Completely new patterns (should generalize)
    [
        'text' => 'vodacom mpesa balance check dial *900# for details',
        'expected' => 'scam',
        'description' => 'New pattern - different provider but same scam type'
    ],
    [
        'text' => 'thank you for your payment your order will ship tomorrow',
        'expected' => 'safe',
        'description' => 'New pattern - legitimate business message'
    ],
    [
        'text' => 'congratulations you have won 100000 tsh click here to claim',
        'expected' => 'scam',
        'description' => 'New pattern - prize scam with different amount'
    ],
    [
        'text' => 'happy birthday hope you have a wonderful day with family',
        'expected' => 'safe',
        'description' => 'New pattern - personal message'
    ],
    
    // Edge cases
    [
        'text' => 'urgent your account will be suspended verify pin immediately call now',
        'expected' => 'scam',
        'description' => 'Complex scam pattern - multiple scam indicators'
    ],
    [
        'text' => 'please find the meeting agenda attached for review',
        'expected' => 'safe',
        'description' => 'Complex legitimate pattern - business communication'
    ]
];

$correctPredictions = 0;
$totalTests = count($testCases);

foreach ($testCases as $index => $testCase) {
    $result = $detector->predict($testCase['text']);
    $prediction = $result['label'];
    $confidence = round($result['confidence'] * 100, 1);
    $reason = $result['reason'];
    
    $isCorrect = ($prediction === $testCase['expected']);
    if ($isCorrect) $correctPredictions++;
    
    $status = $isCorrect ? '✅' : '❌';
    
    echo "Test " . ($index + 1) . ": {$testCase['description']}\\n";
    echo "Text: \\\"{$testCase['text']}\\\"\\n";
    echo "Expected: {$testCase['expected']} | Predicted: {$prediction} | Confidence: {$confidence}% {$status}\\n";
    echo "Reason: {$reason}\\n";
    echo "---\\n\\n";
}

$accuracy = ($correctPredictions / $totalTests) * 100;
echo "=== Results ===\\n";
echo "Correct Predictions: {$correctPredictions}/{$totalTests}\\n";
echo "Accuracy: " . round($accuracy, 1) . "%\\n\\n";

if ($accuracy >= 80) {
    echo "🎉 Excellent! The model is learning patterns effectively!\\n";
} elseif ($accuracy >= 60) {
    echo "👍 Good! The model is learning patterns with room for improvement.\\n";
} else {
    echo "⚠️  The model needs more training data or improvements.\\n";
}

echo "\\n=== Pattern Learning Analysis ===\\n";
echo "If the model correctly identifies variations (not exact matches),\\n";
echo "it's learning patterns. If it only works on exact matches,\\n";
echo "it's doing string matching.\\n";