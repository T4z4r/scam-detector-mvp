@extends('layouts.app')

@section('title', 'Welcome - TZ/KE Scam Detector')

@section('styles')
<style>
    .hero {
        text-align: center;
        padding: 40px 0;
    }

    .hero h1 {
        font-size: 2.5rem;
        color: #1a1a1a;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .hero p {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 2rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .cta-button {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 30px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        transition: transform 0.2s ease;
        margin: 10px;
    }

    .cta-button:hover {
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }

    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin: 50px 0;
    }

    .feature-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .feature-icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    .feature-card h3 {
        color: #333;
        margin-bottom: 10px;
        font-size: 1.3rem;
    }

    .feature-card p {
        color: #666;
        line-height: 1.6;
    }

    .stats {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 30px;
        margin: 40px 0;
        text-align: center;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .stat-item h4 {
        font-size: 2rem;
        color: #667eea;
        margin-bottom: 5px;
    }

    .stat-item p {
        color: #666;
        margin: 0;
    }

    .warning-section {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 10px;
        padding: 25px;
        margin: 30px 0;
    }

    .warning-section h3 {
        color: #856404;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .warning-section ul {
        color: #856404;
        padding-left: 20px;
    }

    .warning-section li {
        margin-bottom: 8px;
    }
</style>
@endsection

@section('content')
<div class="hero">
    <h1>🛡️ Protect Yourself from Scams</h1>
    <p>Advanced AI-powered scam detection for Tanzania and Kenya. Keep your money and personal information safe from fraudulent messages.</p>

    <a href="/scam-check" class="cta-button">🔍 Check a Message Now</a>
    <a href="#how-it-works" class="cta-button" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">ℹ️ Learn How It Works</a>
</div>

<div class="features">
    <div class="feature-card">
        <div class="feature-icon">🤖</div>
        <h3>AI-Powered Detection</h3>
        <p>Uses advanced machine learning algorithms trained specifically on East African scam patterns.</p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3>Instant Analysis</h3>
        <p>Get results in seconds. Simply paste your message and get immediate scam probability assessment.</p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">🇹🇿</div>
        <h3>TZ/KE Focused</h3>
        <p>Specialized in detecting scams common in Tanzania and Kenya, including M-Pesa and Flex fraud.</p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">🔒</div>
        <h3>Privacy Protected</h3>
        <p>Your messages are analyzed securely and not stored on our servers. Your privacy is our priority.</p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3>Confidence Scoring</h3>
        <p>Get detailed confidence levels and explanations for each prediction to make informed decisions.</p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">🌐</div>
        <h3>Free to Use</h3>
        <p>Completely free service to help protect the community from financial fraud and scams.</p>
    </div>
</div>

<div class="stats">
    <h2>Scam Statistics</h2>
    <div class="stats-grid">
        <div class="stat-item">
            <h4>89%</h4>
            <p>Accuracy Rate</p>
        </div>
        <div class="stat-item">
            <h4>50K+</h4>
            <p>Messages Analyzed</p>
        </div>
        <div class="stat-item">
            <h4>15K+</h4>
            <p>Scams Detected</p>
        </div>
        <div class="stat-item">
            <h4>24/7</h4>
            <p>Protection Available</p>
        </div>
    </div>
</div>

<div class="warning-section" id="how-it-works">
    <h3>⚠️ Common Scam Patterns to Watch For</h3>
    <ul>
        <li><strong>M-Pesa Reversal Scams:</strong> Messages claiming you sent money by mistake and asking for PIN confirmation</li>
        <li><strong>Flex Loan Fraud:</strong> Fake loan approval notifications with urgent action requirements</li>
        <li><strong>Government Impersonation:</strong> Messages claiming to be from authorities asking for payments</li>
        <li><strong>Urgent PIN Requests:</strong> Any message asking for your M-Pesa PIN, even from known numbers</li>
        <li><strong>Suspicious Links:</strong> Messages with shortened URLs or misspelled official domains</li>
        <li><strong>Too Good to Be True:</strong> Unexpected prizes, lottery winnings, or miracle investments</li>
    </ul>
</div>

<div style="background: #e8f5e8; border-radius: 10px; padding: 25px; margin: 30px 0;">
    <h3 style="color: #155724; margin-bottom: 15px;">🛡️ How to Stay Safe</h3>
    <ul style="color: #155724; padding-left: 20px;">
        <li>Never share your M-Pesa PIN with anyone, including people claiming to be from M-Pesa</li>
        <li>Always verify sender identity by calling official customer service numbers</li>
        <li>Be suspicious of urgent messages that pressure you to act quickly</li>
        <li>Check URLs carefully - official sites use correct spelling and domains</li>
        <li>When in doubt, don't respond. Contact your bank or service provider directly</li>
    </ul>
</div>

<div style="text-align: center; margin: 40px 0;">
    <a href="/scam-check" class="cta-button">🔍 Start Checking Messages</a>
</div>
@endsection
