<?php

require 'vendor/autoload.php';

use App\Services\SpamDetector;

try {
    echo "Testing Spam Detector..." . PHP_EOL;

    $detector = new SpamDetector();

    // Test with scam message
    echo "1. Testing scam message..." . PHP_EOL;
    $result1 = $detector->predict('mpesa reversal ksh 2500 click http://bit.ly/xyz confirm pin now', 'MPESA');
    echo "Result: " . json_encode($result1, JSON_PRETTY_PRINT) . PHP_EOL;
    echo "Label: " . $result1['label'] . " (Expected: scam)" . PHP_EOL;
    echo "Confidence: " . $result1['confidence'] . PHP_EOL;
    echo "Reason: " . $result1['reason'] . PHP_EOL;
    echo PHP_EOL;

    // Test with safe message
    echo "2. Testing safe message..." . PHP_EOL;
    $result2 = $detector->predict('meeting at 3pm today please confirm attendance', 'JOHN');
    echo "Result: " . json_encode($result2, JSON_PRETTY_PRINT) . PHP_EOL;
    echo "Label: " . $result2['label'] . " (Expected: safe)" . PHP_EOL;
    echo "Confidence: " . $result2['confidence'] . PHP_EOL;
    echo "Reason: " . $result2['reason'] . PHP_EOL;
    echo PHP_EOL;

    // Test with another scam pattern
    echo "3. Testing another scam pattern..." . PHP_EOL;
    $result3 = $detector->predict('flex loan approved get 50000 tsh instantly apply now www.fake-loan.com', 'FLEX');
    echo "Result: " . json_encode($result3, JSON_PRETTY_PRINT) . PHP_EOL;
    echo "Label: " . $result3['label'] . " (Expected: scam)" . PHP_EOL;
    echo "Confidence: " . $result3['confidence'] . PHP_EOL;
    echo "Reason: " . $result3['reason'] . PHP_EOL;

    echo PHP_EOL . "All tests completed!" . PHP_EOL;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}
