<!DOCTYPE html>
<html>
<head>
    <title>TZ/KE Scam Detector MVP</title>
</head>
<body>
    <h1>🚨 Spam/Scam Checker</h1>
    <form method="POST" action="/scam-check">
        @csrf
        <label>SMS Text:</label><br>
        <textarea name="text" rows="4" cols="50"></textarea><br>
        <label>Sender (optional):</label><br>
        <input type="text" name="sender"><br>
        <button type="submit">Check</button>
    </form>

    @if(isset($result))
        <h2>Result: {{ $result['label'] === 'scam' ? '🚨 SCAM!' : '✅ Safe' }}</h2>
        <p>Confidence: {{ round($result['confidence'] * 100, 2) }}%</p>
        <p>Reason: {{ $result['reason'] }}</p>
    @endif
</body>
</html>