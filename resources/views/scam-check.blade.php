i<!DOCTYPE html>
<html>
<head>
    <title>TZ/KE Scam Detector MVP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .scam { color: red; }
        .safe { color: green; }
        .error { color: orange; background: #ffe6e6; padding: 10px; border-radius: 5px; }
        .result { margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        textarea, input { width: 100%; max-width: 400px; margin: 5px 0; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🚨 Spam/Scam Checker</h1>
    <form method="POST" action="/scam-check">
        @csrf
        <label>SMS Text:</label><br>
        <textarea name="text" rows="4" cols="50">{{ old('text') }}</textarea><br>
        <label>Sender (optional):</label><br>
        <input type="text" name="sender" value="{{ old('sender') }}"><br>
        <button type="submit">Check</button>
    </form>

    @if(isset($error))
        <div class="error">
            <strong>Error:</strong> {{ $error }}
        </div>
    @endif

    @if(isset($result))
        <div class="result">
            <h2 class="{{ $result['label'] === 'scam' ? 'scam' : 'safe' }}">
                Result: {{ $result['label'] === 'scam' ? '🚨 SCAM!' : '✅ Safe' }}
            </h2>
            <p><strong>Confidence:</strong> {{ round($result['confidence'] * 100, 2) }}%</p>
            <p><strong>Reason:</strong> {{ $result['reason'] }}</p>
            @if($result['label'] === 'scam')
                <div class="error">
                    <strong>⚠️ Warning:</strong> This appears to be a scam. Do not respond, block the sender, and report to authorities (e.g., 333 in TZ/KE).
                </div>
            @else
                <div style="color: green; background: #e6ffe6; padding: 10px; border-radius: 5px;">
                    <strong>✅ Information:</strong> This message appears to be safe, but always exercise caution.
                </div>
            @endif
        </div>
    @endif
</body>
</html>
