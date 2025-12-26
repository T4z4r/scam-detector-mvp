@extends('layouts.app')

@section('title', 'Check Messages - TZ/KE Scam Detector')

@section('styles')
<style>
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #555;
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .textarea {
        min-height: 120px;
        resize: vertical;
    }

    .btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .btn:active {
        transform: translateY(0);
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
        border-left: 4px solid;
    }

    .alert-danger {
        background: #ffe6e6;
        border-color: #dc3545;
        color: #721c24;
    }

    .alert-success {
        background: #e6ffe6;
        border-color: #28a745;
        color: #155724;
    }

    .alert-warning {
        background: #fff3cd;
        border-color: #ffc107;
        color: #856404;
    }

    .result-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin: 20px 0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border-left: 5px solid;
    }

    .result-card.scam {
        border-left-color: #dc3545;
    }

    .result-card.safe {
        border-left-color: #28a745;
    }

    .result-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .result-icon {
        font-size: 24px;
        margin-right: 10px;
    }

    .result-title {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    .result-card.scam .result-title {
        color: #dc3545;
    }

    .result-card.safe .result-title {
        color: #28a745;
    }

    .confidence-bar {
        background: #f0f0f0;
        border-radius: 10px;
        height: 10px;
        margin: 10px 0;
        overflow: hidden;
    }

    .confidence-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .confidence-high {
        background: linear-gradient(90deg, #28a745, #20c997);
    }

    .confidence-medium {
        background: linear-gradient(90deg, #ffc107, #fd7e14);
    }

    .confidence-low {
        background: linear-gradient(90deg, #dc3545, #e83e8c);
    }

    .sample-messages {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
    }

    .sample-message {
        background: white;
        padding: 10px;
        margin: 8px 0;
        border-radius: 5px;
        border-left: 3px solid #667eea;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .sample-message:hover {
        background: #f0f0f0;
    }

    .info-section {
        background: #e3f2fd;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }

    .info-section h3 {
        color: #1976d2;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="form-container">
    <h2>Check Your Message</h2>
    <p>Paste the suspicious SMS or message below to analyze if it's a scam.</p>

    <form method="POST" action="/scam-check">
        @csrf

        <div class="form-group">
            <label for="text">Message Content *</label>
            <textarea
                class="form-control textarea"
                name="text"
                id="text"
                placeholder="Paste the suspicious message here..."
                required
            >{{ old('text') }}</textarea>
        </div>

        <div class="form-group">
            <label for="sender">Sender (Optional)</label>
            <input
                type="text"
                class="form-control"
                name="sender"
                id="sender"
                placeholder="e.g., MPESA, FLEX, +254700000000"
                value="{{ old('sender') }}"
            >
        </div>

        <button type="submit" class="btn">🔍 Analyze Message</button>
    </form>
</div>

@if(isset($error))
    <div class="alert alert-danger">
        <strong>❌ Error:</strong> {{ $error }}
    </div>
@endif

@if(isset($result))
    <div class="result-card {{ $result['label'] }}">
        <div class="result-header">
            <span class="result-icon">
                @if($result['label'] === 'scam')
                    🚨
                @else
                    ✅
                @endif
            </span>
            <h3 class="result-title">
                @if($result['label'] === 'scam')
                    Potential Scam Detected
                @else
                    Message Appears Safe
                @endif
            </h3>
        </div>

        <div class="confidence-section">
            <strong>Confidence Level:</strong>
            <div class="confidence-bar">
                @php
                    $confidencePercent = round($result['confidence'] * 100);
                    $confidenceClass = $confidencePercent >= 80 ? 'confidence-high' : ($confidencePercent >= 50 ? 'confidence-medium' : 'confidence-low');
                @endphp
                <div class="confidence-fill {{ $confidenceClass }}" style="width: {{ $confidencePercent }}%"></div>
            </div>
            <p>{{ $confidencePercent }}% confidence</p>
        </div>

        <div class="analysis-reason">
            <strong>Analysis Method:</strong> {{ $result['reason'] }}
        </div>

        @if($result['label'] === 'scam')
            <div class="alert alert-danger">
                <h4>⚠️ Safety Recommendations:</h4>
                <ul>
                    <li><strong>Do not respond</strong> to this message</li>
                    <li><strong>Block the sender</strong> immediately</li>
                    <li><strong>Do not share</strong> personal information, PINs, or passwords</li>
                    <li><strong>Report to authorities:</strong></li>
                    <ul>
                        <li>Kenya: Call <strong>999</strong> or visit <a href="https://www.cck.go.ke/" target="_blank">CCK Website</a></li>
                        <li>Tanzania: Call <strong>333</strong></li>
                    </ul>
                </ul>
            </div>
        @else
            <div class="alert alert-success">
                <h4>✅ Safety Information:</h4>
                <p>This message appears to be legitimate based on our analysis. However, always exercise caution and verify sender identity before sharing sensitive information.</p>
            </div>
        @endif
    </div>
@endif

<div class="sample-messages">
    <h3>💡 Try These Sample Messages:</h3>
    <div class="sample-message" onclick="fillMessage('mpesa reversal ksh 2500 click http://bit.ly/xyz confirm pin now')">
        <strong>Scam Example:</strong> "mpesa reversal ksh 2500 click http://bit.ly/xyz confirm pin now"
    </div>
    <div class="sample-message" onclick="fillMessage('meeting at 3pm today please confirm attendance')">
        <strong>Safe Example:</strong> "meeting at 3pm today please confirm attendance"
    </div>
    <div class="sample-message" onclick="fillMessage('flex loan approved get 50000 tsh instantly apply now www.fake-loan.com')">
        <strong>Scam Example:</strong> "flex loan approved get 50000 tsh instantly apply now www.fake-loan.com"
    </div>
</div>

<div class="info-section">
    <h3>🛡️ How It Works</h3>
    <p>Our scam detector uses advanced machine learning and pattern recognition to identify potentially fraudulent messages. It specifically looks for:</p>
    <ul>
        <li><strong>TZ/KE Scam Patterns:</strong> M-Pesa, Flex, Godi reversal scams</li>
        <li><strong>Suspicious Keywords:</strong> PIN requests, reversal mentions, urgent language</li>
        <li><strong>URL Patterns:</strong> Shortened links, fake websites</li>
        <li><strong>Financial Terms:</strong> Unusual amounts, loan promises</li>
    </ul>
</div>
@endsection

@section('scripts')
<script>
    function fillMessage(message) {
        document.getElementById('text').value = message;
        document.getElementById('text').focus();
    }

    // Auto-resize textarea
    document.getElementById('text').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
</script>
@endsection
