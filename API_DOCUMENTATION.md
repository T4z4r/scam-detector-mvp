# 📡 TZ/KE Scam Detector API Documentation

> Complete API reference for the Scam Detection Service

## 📋 Table of Contents

- [Overview](#overview)
- [Base URL](#base-url)
- [Authentication](#authentication)
- [Endpoints](#endpoints)
- [Request Format](#request-format)
- [Response Format](#response-format)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)
- [Examples](#examples)
- [SDKs and Libraries](#sdks-and-libraries)
- [Changelog](#changelog)

## 🔍 Overview

The TZ/KE Scam Detector API provides programmatic access to our machine learning-powered scam detection service. The API allows you to analyze SMS messages and other text content to determine the likelihood of being a scam, specifically trained on Tanzania and Kenya fraud patterns.

### Key Features
- **High Accuracy**: 89% detection accuracy on TZ/KE scam patterns
- **Fast Response**: Average response time under 200ms
- **Specialized Detection**: Optimized for East African scam tactics
- **Confidence Scoring**: Detailed probability assessments
- **Privacy Focused**: No message storage or logging

## 🌐 Base URL

```
Production: https://api.your-domain.com/v1
Development: http://localhost:8000/api/v1
```

## 🔐 Authentication

Currently, the API is open and does not require authentication. This may change in future versions.

**Future Authentication:**
```http
Authorization: Bearer YOUR_API_KEY
```

## 📡 Endpoints

### POST /scam/check

Analyze a message for scam probability.

#### Endpoint Details
- **URL**: `/scam/check`
- **Method**: `POST`
- **Content-Type**: `application/json`
- **Rate Limit**: 100 requests per minute per IP

#### Request Body

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `text` | string | Yes | The message content to analyze (max 1000 characters) |
| `sender` | string | No | The sender identifier (phone number, name, etc., max 50 characters) |

#### Example Request

```bash
curl -X POST https://api.your-domain.com/v1/scam/check \
  -H "Content-Type: application/json" \
  -d '{
    "text": "mpesa reversal ksh 2500 click http://bit.ly/xyz confirm pin now",
    "sender": "MPESA"
  }'
```

#### Response Format

```json
{
  "success": true,
  "data": {
    "result": "scam",
    "confidence": "99%",
    "reason": "Matches TZ/KE scam patterns (e.g., M-Pesa reversal)",
    "alert": "🚨 SCAM DETECTED! Ignore, block sender, and report to authorities (e.g., 333 in TZ/KE).",
    "analysis": {
      "method": "ml_classification",
      "confidence_score": 0.99,
      "processing_time_ms": 145,
      "model_version": "1.2.0"
    }
  },
  "timestamp": "2025-12-27T16:37:00Z",
  "request_id": "req_1234567890abcdef"
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Whether the request was successful |
| `data.result` | string | Prediction result: `"scam"`, `"safe"`, or `"unknown"` |
| `data.confidence` | string | Human-readable confidence percentage |
| `data.reason` | string | Explanation of why this result was chosen |
| `data.alert` | string | User-friendly warning or safety message |
| `data.analysis.method` | string | Detection method used |
| `data.analysis.confidence_score` | number | Raw confidence score (0.0 to 1.0) |
| `data.analysis.processing_time_ms` | number | Processing time in milliseconds |
| `data.analysis.model_version` | string | ML model version used |
| `timestamp` | string | ISO 8601 timestamp of response |
| `request_id` | string | Unique request identifier for debugging |

## 📝 Request Format

### Content Type
All POST requests must include:
```http
Content-Type: application/json
```

### Character Limits
- **Message text**: Maximum 1000 characters
- **Sender identifier**: Maximum 50 characters
- **Total request size**: Maximum 10KB

### Text Encoding
- **Character encoding**: UTF-8
- **Special characters**: Fully supported including emojis and non-ASCII characters

### Example Valid Request Bodies

```json
{
  "text": "Hello, this is a legitimate message about our meeting tomorrow at 3pm.",
  "sender": "John Doe"
}
```

```json
{
  "text": "URGENT: Your M-Pesa account will be suspended unless you confirm your PIN immediately. Call 0712345678 now!",
  "sender": "+254700000000"
}
```

```json
{
  "text": "You have won $1000 in our lucky draw! Click here: http://bit.ly/fake-prize"
}
```

## 📊 Response Format

### Success Response Structure

```json
{
  "success": true,
  "data": {
    "result": "safe",
    "confidence": "85%",
    "reason": "ML classification",
    "alert": "✅ Message appears safe, but always exercise caution.",
    "analysis": {
      "method": "ml_classification",
      "confidence_score": 0.85,
      "processing_time_ms": 123,
      "model_version": "1.2.0",
      "features_detected": ["normal_greeting", "legitimate_context"]
    }
  },
  "timestamp": "2025-12-27T16:37:00Z",
  "request_id": "req_abcdef1234567890"
}
```

### Result Types

| Result | Description | Action |
|--------|-------------|--------|
| `scam` | High probability of being a scam | **DO NOT RESPOND** - Block sender, report to authorities |
| `safe` | Low probability of being a scam | **LIKELY LEGITIMATE** - Exercise normal caution |
| `unknown` | Insufficient data or model error | **UNCERTAIN** - Use additional verification methods |

### Confidence Levels

| Confidence Range | Interpretation |
|------------------|----------------|
| 90% - 100% | Very high confidence in prediction |
| 70% - 89% | High confidence, reliable result |
| 50% - 69% | Moderate confidence, consider additional factors |
| Below 50% | Low confidence, treat with caution |

## ⚠️ Error Handling

### HTTP Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request successful |
| 400 | Bad Request | Invalid request format or parameters |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error occurred |
| 503 | Service Unavailable | Service temporarily unavailable |

### Error Response Format

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The text field is required and must not exceed 1000 characters.",
    "details": {
      "field": "text",
      "constraint": "required|max:1000"
    }
  },
  "timestamp": "2025-12-27T16:37:00Z",
  "request_id": "req_error123456"
}
```

### Common Error Codes

| Code | Description | Resolution |
|------|-------------|------------|
| `VALIDATION_ERROR` | Request validation failed | Check request parameters |
| `RATE_LIMIT_EXCEEDED` | Too many requests | Wait before retrying |
| `MODEL_NOT_AVAILABLE` | ML model not loaded | Contact support |
| `TEXT_TOO_LONG` | Message exceeds 1000 chars | Truncate or split message |
| `INVALID_JSON` | Malformed JSON request | Fix JSON syntax |

## 🚦 Rate Limiting

### Current Limits
- **Requests per minute**: 100 per IP address
- **Requests per hour**: 1000 per IP address
- **Requests per day**: 10000 per IP address

### Rate Limit Headers

Each response includes rate limiting information:

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
X-RateLimit-Window: 60
```

### Handling Rate Limits

When rate limit is exceeded:

```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Rate limit exceeded. Try again in 45 seconds.",
    "retry_after": 45
  },
  "timestamp": "2025-12-27T16:37:00Z"
}
```

## 💡 Examples

### cURL Examples

#### Basic Scam Detection
```bash
curl -X POST https://api.your-domain.com/v1/scam/check \
  -H "Content-Type: application/json" \
  -d '{
    "text": "Congratulations! You have won $5000. Click here to claim: http://fake-prize.com",
    "sender": "PRIZE_NOTIFY"
  }'
```

#### Safe Message Test
```bash
curl -X POST https://api.your-domain.com/v1/scam/check \
  -H "Content-Type: application/json" \
  -d '{
    "text": "Hi John, just a reminder about our meeting tomorrow at 2pm. See you then!",
    "sender": "Sarah"
  }'
```

#### M-Pesa Scam Example
```bash
curl -X POST https://api.your-domain.com/v1/scam/check \
  -H "Content-Type: application/json" \
  -d '{
    "text": "M-Pesa reversal Ksh 2500 pending. Confirm PIN to complete reversal. Call 0712345678",
    "sender": "MPESA"
  }'
```

### JavaScript/Node.js Example

```javascript
const axios = require('axios');

async function checkScam(message, sender = '') {
  try {
    const response = await axios.post('https://api.your-domain.com/v1/scam/check', {
      text: message,
      sender: sender
    }, {
      headers: {
        'Content-Type': 'application/json'
      }
    });
    
    const { result, confidence, reason } = response.data.data;
    
    if (result === 'scam') {
      console.log(`🚨 SCAM DETECTED (${confidence}): ${reason}`);
      return false; // Don't trust this message
    } else if (result === 'safe') {
      console.log(`✅ Message appears safe (${confidence})`);
      return true; // Likely safe to trust
    } else {
      console.log(`❓ Uncertain (${confidence}): ${reason}`);
      return null; // Need additional verification
    }
  } catch (error) {
    console.error('Error checking message:', error.response?.data?.error?.message);
    return null;
  }
}

// Usage
checkScam('M-Pesa reversal Ksh 2500 pending. Confirm PIN now!', 'MPESA');
```

### Python Example

```python
import requests
import json

def check_scam(message, sender=''):
    url = 'https://api.your-domain.com/v1/scam/check'
    data = {
        'text': message,
        'sender': sender
    }
    
    try:
        response = requests.post(url, json=data, headers={
            'Content-Type': 'application/json'
        })
        
        if response.status_code == 200:
            result = response.json()
            data = result['data']
            
            print(f"Result: {data['result']}")
            print(f"Confidence: {data['confidence']}")
            print(f"Reason: {data['reason']}")
            
            return data['result']
        else:
            print(f"Error: {response.status_code}")
            return None
            
    except requests.RequestException as e:
        print(f"Request failed: {e}")
        return None

# Usage
result = check_scam('You have won a lottery! Send your details to claim.', 'LOTTERY')
```

### PHP Example

```php
<?php

function checkScam($message, $sender = '') {
    $url = 'https://api.your-domain.com/v1/scam/check';
    $data = [
        'text' => $message,
        'sender' => $sender
    ];
    
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $result = json_decode($response, true);
        
        if ($result['success']) {
            $data = $result['data'];
            echo "Result: {$data['result']}\n";
            echo "Confidence: {$data['confidence']}\n";
            echo "Reason: {$data['reason']}\n";
            
            return $data['result'];
        }
    }
    
    return null;
}

// Usage
$result = checkScam('URGENT: Your bank account will be closed!', 'BANK_SECURITY');
?>
```

## 📚 SDKs and Libraries

### Official Libraries

We plan to provide official SDKs for popular languages:

- **JavaScript/Node.js**: `@scam-detector/sdk`
- **Python**: `scam-detector-python`
- **PHP**: `scam-detector/php-sdk`
- **Java**: `com.scamdetector:java-sdk`
- **C#**: `ScamDetector.Net`

### Third-Party Integrations

#### Laravel Package
```bash
composer require scam-detector/laravel
```

#### WordPress Plugin
Available in WordPress plugin repository.

#### Mobile App Integration
React Native and Flutter packages coming soon.

## 📈 Usage Analytics

### Response Headers for Analytics
```http
X-Analytics-Requests: 15234
X-Analytics-Success-Rate: 98.5%
X-Analytics-Avg-Response-Time: 145ms
```

## 🔄 Changelog

### Version 1.2.0 (Current)
- **New**: Enhanced M-Pesa scam detection
- **Improved**: Confidence scoring algorithm
- **Added**: Request analytics headers
- **Fixed**: Rate limiting edge cases

### Version 1.1.0
- **New**: Flex loan scam patterns
- **Added**: Processing time metrics
- **Improved**: Error message clarity
- **Fixed**: UTF-8 character handling

### Version 1.0.0
- **Initial Release**
- **Core**: Scam detection API
- **Features**: Basic web interface
- **Support**: TZ/KE scam patterns

## 🆘 Support

### Getting Help
- **Documentation**: This file and our main README
- **GitHub Issues**: [Create an issue](https://github.com/your-org/scam-detector-mvp/issues)
- **Community**: Join our discussions
- **Email**: api-support@your-domain.com

### Status Page
- **API Status**: https://status.your-domain.com
- **Uptime**: 99.9% target
- **Status Updates**: @scamdetectorstatus

### SLA
- **Response Time**: < 500ms for 95% of requests
- **Uptime**: 99.5% monthly availability
- **Support**: 24/7 for critical issues
- **Updates**: Monthly model improvements

---

**API Version**: 1.2.0  
**Last Updated**: December 27, 2025  
**Base URL**: https://api.your-domain.com/v1

*For the most up-to-date API documentation, visit [docs.your-domain.com](https://docs.your-domain.com)*
