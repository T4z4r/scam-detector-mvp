# 📡 Tanzania Scam Detector API Documentation

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

The Tanzania Scam Detector API provides programmatic access to our machine learning-powered scam detection service. The API allows you to analyze SMS messages and other text content to determine the likelihood of being a scam, specifically trained on Tanzania fraud patterns.

### Key Features
- **High Accuracy**: 89% detection accuracy on Tanzania scam patterns
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

**Success Response (200 OK)**
```json
{
  "status": "success",
  "message": "Scam detected in message",
  "data": {
    "result": "scam",
    "confidence": "99%",
    "reason": "Matches TZ scam patterns (e.g., M-Pesa reversal)",
    "alert": "🚨 SCAM DETECTED! Ignore, block sender, and report to authorities (e.g., 333 in Tanzania).",
    "risk_level": "high"
  }
}
```

**Error Response (422 Unprocessable Entity)**
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "text": ["The text field is required and must not exceed 1000 characters."]
  }
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `status` | string | Response status: `"success"` or `"error"` |
| `message` | string | Descriptive success or error message |
| `data.result` | string | Prediction result: `"scam"`, `"safe"`, or `"unknown"` |
| `data.confidence` | string | Human-readable confidence percentage |
| `data.reason` | string | Explanation of why this result was chosen |
| `data.alert` | string | User-friendly warning or safety message |
| `data.risk_level` | string | Risk assessment: `"high"` or `"low"` |
| `errors` | object | Validation errors (only present on error responses) |

### POST /training/train

Start or retrain the spam detection model.

#### Endpoint Details
- **URL**: `/training/train`
- **Method**: `POST`
- **Content-Type**: `application/json`
- **Rate Limit**: 10 requests per hour per IP

#### Request Body

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Action to perform: `"train"` or `"retrain"` |

#### Example Request

```bash
curl -X POST https://api.your-domain.com/v1/training/train \
  -H "Content-Type: application/json" \
  -d '{
    "action": "train"
  }'
```

#### Response Format

**Success Response (200 OK)**
```json
{
  "status": "success",
  "message": "Model training completed successfully!",
  "data": {
    "modelStatus": {
      "is_trained": true,
      "model_size": 24576,
      "last_trained": "2025-12-28 19:43:00",
      "path": "/storage/app/spam_model.phpml"
    },
    "action": "train"
  }
}
```

**Error Response (404 Not Found)**
```json
{
  "status": "error",
  "message": "Training dataset not found. Please upload training data first."
}
```

**Error Response (500 Internal Server Error)**
```json
{
  "status": "error",
  "message": "Training process failed: Training command failed with exit code: 1"
}
```

### GET /training/status

Check the current training status and model information.

#### Endpoint Details
- **URL**: `/training/status`
- **Method**: `GET`
- **Rate Limit**: 60 requests per minute per IP

#### Example Request

```bash
curl -X GET https://api.your-domain.com/v1/training/status
```

#### Response Format

**Success Response (200 OK)**
```json
{
  "status": "success",
  "message": "Training status retrieved successfully",
  "data": {
    "modelStatus": {
      "is_trained": true,
      "model_size": 24576,
      "last_trained": "2025-12-28 19:43:00",
      "path": "/storage/app/spam_model.phpml"
    },
    "data": {
      "exists": true,
      "total_samples": 1500,
      "spam_count": 750,
      "ham_count": 750,
      "file_size": 45678,
      "last_modified": "2025-12-28 19:40:00",
      "sample_data": [
        {
          "label": "spam",
          "message": "Congratulations! You have won $5000. Click here to claim..."
        }
      ]
    }
  }
}
```

**Error Response (500 Internal Server Error)**
```json
{
  "status": "error",
  "message": "Failed to retrieve training status: Unable to access training data"
}
```

### GET /training/data

Get training data statistics and sample data.

#### Endpoint Details
- **URL**: `/training/data`
- **Method**: `GET`
- **Rate Limit**: 60 requests per minute per IP

#### Response Format

**Success Response (200 OK)**
```json
{
  "status": "success",
  "message": "Training data statistics retrieved successfully",
  "data": {
    "exists": true,
    "total_samples": 1500,
    "spam_count": 750,
    "ham_count": 750,
    "file_size": 45678,
    "last_modified": "2025-12-28 19:40:00",
    "sample_data": [
      {
        "label": "spam",
        "message": "Congratulations! You have won $5000. Click here to claim..."
      }
    ]
  }
}
```

**Error Response (500 Internal Server Error)**
```json
{
  "status": "error",
  "message": "Failed to retrieve training data: Unable to read dataset file"
}
```

### POST /training/upload

Upload training data file (CSV format).

#### Endpoint Details
- **URL**: `/training/upload`
- **Method**: `POST`
- **Content-Type**: `multipart/form-data`
- **Rate Limit**: 5 requests per hour per IP

#### Request Body

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `training_file` | file | Yes | CSV file with training data (max 10MB) |

#### Example Request

```bash
curl -X POST https://api.your-domain.com/v1/training/upload \
  -F "training_file=@training_data.csv"
```

#### Response Format

**Success Response (201 Created)**
```json
{
  "status": "success",
  "message": "Training data uploaded successfully!",
  "data": {
    "exists": true,
    "total_samples": 1500,
    "spam_count": 750,
    "ham_count": 750,
    "file_size": 45678,
    "last_modified": "2025-12-28 19:43:00"
  }
}
```

**Error Response (422 Unprocessable Entity)**
```json
{
  "status": "error",
  "message": "No valid training data found. Format should be: label\\tmessage (tab-separated)"
}
```

**Error Response (500 Internal Server Error)**
```json
{
  "status": "error",
  "message": "Failed to upload training data: File upload failed"
}
```

### DELETE /training/data

Delete the current training dataset.

#### Endpoint Details
- **URL**: `/training/data`
- **Method**: `DELETE`
- **Rate Limit**: 3 requests per hour per IP

#### Example Request

```bash
curl -X DELETE https://api.your-domain.com/v1/training/data
```

#### Response Format

**Success Response (200 OK)**
```json
{
  "status": "success",
  "message": "Training data deleted successfully!"
}
```

**Error Response (404 Not Found)**
```json
{
  "status": "error",
  "message": "Training data file not found"
}
```

**Error Response (500 Internal Server Error)**
```json
{
  "status": "error",
  "message": "Failed to delete training data: Permission denied"
}
```

### GET /training/metrics

Get detailed model performance metrics.

#### Endpoint Details
- **URL**: `/training/metrics`
- **Method**: `GET`
- **Rate Limit**: 60 requests per minute per IP

#### Response Format

**Success Response (200 OK)**
```json
{
  "status": "success",
  "message": "Model metrics retrieved successfully",
  "data": {
    "total_samples": 1500,
    "spam_samples": 750,
    "ham_samples": 750,
    "spam_ratio": 50.0,
    "ham_ratio": 50.0,
    "is_trained": true,
    "model_size": 24576,
    "last_trained": "2025-12-28 19:43:00"
  }
}
```

**Error Response (500 Internal Server Error)**
```json
{
  "status": "error",
  "message": "Failed to retrieve model metrics: Model file not accessible"
}
```

### POST /feedback/scam-message

Report a scam message to help improve the model.

#### Endpoint Details
- **URL**: `/feedback/scam-message`
- **Method**: `POST`
- **Content-Type**: `application/json`
- **Rate Limit**: 50 requests per hour per IP

#### Request Body

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `message_text` | string | Yes | The scam message content (max 1000 characters) |
| `sender_identifier` | string | No | The sender identifier (phone number, name, etc., max 50 characters) |
| `sender_type` | string | No | Type of sender: `phone`, `email`, `name`, or `other` (default: `other`) |
| `user_id` | string | No | Optional user identifier for tracking |

#### Example Request

```bash
curl -X POST https://api.your-domain.com/v1/feedback/scam-message \
  -H "Content-Type: application/json" \
  -d '{
    "message_text": "M-Pesa reversal Ksh 2500 pending. Confirm PIN to complete reversal. Call 0712345678",
    "sender_identifier": "+254712345678",
    "sender_type": "phone",
    "user_id": "user123"
  }'
```

#### Response Format

**Success Response (201 Created)**
```json
{
  "status": "success",
  "message": "Scam message reported successfully. Thank you for helping improve our detection!",
  "data": {
    "feedback_id": 12345,
    "message": "Your report helps train our AI to detect similar scams better."
  }
}
```

### POST /feedback/scam-sender

Report a known scam sender/contact.

#### Endpoint Details
- **URL**: `/feedback/scam-sender`
- **Method**: `POST`
- **Content-Type**: `application/json`
- **Rate Limit**: 50 requests per hour per IP

#### Request Body

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `sender_identifier` | string | Yes | The sender identifier (max 50 characters) |
| `sender_type` | string | Yes | Type of sender: `phone`, `email`, `name`, or `other` |
| `additional_info` | string | No | Additional context about the sender (max 500 characters) |
| `user_id` | string | No | Optional user identifier for tracking |

#### Example Request

```bash
curl -X POST https://api.your-domain.com/v1/feedback/scam-sender \
  -H "Content-Type: application/json" \
  -d '{
    "sender_identifier": "+254712345678",
    "sender_type": "phone",
    "additional_info": "Constantly sends M-Pesa reversal scams",
    "user_id": "user123"
  }'
```

#### Response Format

**Success Response (201 Created)**
```json
{
  "status": "success",
  "message": "Scam sender reported successfully. This information will help protect others.",
  "data": {
    "sender_id": 123,
    "message": "We\'ve added this sender to our database of known scammers."
  }
}
```

### POST /feedback/false-positive

Report a false positive (legitimate message flagged as scam).

#### Endpoint Details
- **URL**: `/feedback/false-positive`
- **Method**: `POST`
- **Content-Type**: `application/json`
- **Rate Limit**: 50 requests per hour per IP

#### Request Body

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `message_text` | string | Yes | The legitimate message content (max 1000 characters) |
| `sender_identifier` | string | No | The sender identifier (max 50 characters) |
| `reason` | string | No | Why you believe this is a false positive (max 200 characters) |
| `user_id` | string | No | Optional user identifier for tracking |

#### Example Request

```bash
curl -X POST https://api.your-domain.com/v1/feedback/false-positive \
  -H "Content-Type: application/json" \
  -d '{
    "message_text": "Hi John, your appointment is confirmed for tomorrow at 2pm. See you then!",
    "sender_identifier": "Clinic Booking",
    "reason": "Legitimate appointment reminder from my doctor",
    "user_id": "user123"
  }'
```

#### Response Format

**Success Response (201 Created)**
```json
{
  "status": "success",
  "message": "False positive reported successfully. We\'ll use this to improve our model.",
  "data": {
    "feedback_id": 12346,
    "message": "Thank you for helping us reduce false positives!"
  }
}
```

### GET /feedback/stats

Get feedback statistics and system performance.

#### Endpoint Details
- **URL**: `/feedback/stats`
- **Method**: `GET`
- **Rate Limit**: 30 requests per hour per IP

#### Example Request

```bash
curl -X GET https://api.your-domain.com/v1/feedback/stats
```

#### Response Format

**Success Response (200 OK)**
```json
{
  "status": "success",
  "message": "Feedback statistics retrieved successfully",
  "data": {
    "total_feedback": 1250,
    "scam_messages_reported": 850,
    "scam_senders_reported": 320,
    "false_positives_reported": 80,
    "verified_training_samples": 920,
    "known_scam_senders": 156,
    "model_last_trained": "2025-12-30 10:30:00",
    "accuracy_improvement": "+2.3% this month"
  }
}
```

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
  "status": "success",
  "message": "Message appears safe",
  "data": {
    "result": "safe",
    "confidence": "85%",
    "reason": "ML classification",
    "alert": "✅ Message appears safe, but always exercise caution.",
    "risk_level": "low"
  }
}
```

### Error Response Structure

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "text": ["The text field is required and must not exceed 1000 characters."]
  }
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
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request format or parameters |
| 422 | Unprocessable Entity | Validation errors |
| 404 | Not Found | Requested resource not found |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error occurred |
| 503 | Service Unavailable | Service temporarily unavailable |

### Error Response Format

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "text": ["The text field is required and must not exceed 1000 characters."]
  }
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

## 🤖 Continuous Learning System

### Overview
Our system includes an advanced continuous learning mechanism that automatically improves detection accuracy based on user feedback:

#### Key Features
- **Automatic Feedback Processing**: User reports are automatically analyzed and added to training data
- **Model Retraining**: System retrains when sufficient new data is available (minimum 10 new samples)
- **Sender Reputation Database**: Known scammers are tracked for immediate detection
- **Verification System**: User-submitted content is verified before being added to training data
- **Scheduled Maintenance**: Daily model retraining and weekly data cleanup

#### Learning Pipeline
1. **User Reports**: Users report scam messages, senders, or false positives
2. **Automatic Processing**: Reports are processed every 6 hours
3. **Data Verification**: Content is verified for accuracy and quality
4. **Model Updates**: Verified data is added to training dataset
5. **Automatic Retraining**: Model retrains daily when new data is available
6. **Performance Monitoring**: System tracks accuracy improvements

#### Console Commands
```bash
# Process feedback and optionally retrain model
php artisan scam:process-feedback --auto-retrain

# Database backup and maintenance
php artisan scam:backup-maintenance --all

# Check system status
php artisan scam:backup-maintenance --backup
```

#### Scheduled Tasks
- **Every 6 hours**: Process unprocessed feedback
- **Daily at 2 AM**: Process feedback and retrain model (if needed)
- **Weekly**: Database cleanup and backup

### Model Performance Tracking
The system continuously monitors:
- **Accuracy improvement**: Tracks detection accuracy over time
- **False positive reduction**: Monitors and reduces incorrect flagging
- **Training data growth**: Shows expansion of verified training dataset
- **Sender detection success**: Measures effectiveness of known scammer database

## 🔄 Changelog

### Version 1.3.0 (Current)
- **Enhanced**: Standardized API response format with consistent status codes
- **Added**: Proper HTTP status codes for all endpoints (200, 201, 404, 422, 500)
- **Improved**: Error handling with structured error responses
- **Updated**: Response format uses `status` field instead of `success` boolean
- **Added**: Risk level categorization in scam detection responses
- **Improved**: Training endpoint responses with detailed status information

### Version 1.2.0
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
- **Support**: Tanzania scam patterns

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

**API Version**: 1.3.0  
**Last Updated**: December 30, 2025  
**Base URL**: https://api.your-domain.com/v1

*For the most up-to-date API documentation, visit [docs.your-domain.com](https://docs.your-domain.com)*
