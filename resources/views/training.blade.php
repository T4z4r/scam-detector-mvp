@extends('layouts.app')

@section('title', 'Model Training - TZ/KE Scam Detector')

@section('styles')
<style>
    .training-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .status-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border-left: 5px solid;
    }

    .status-card.model-ready {
        border-left-color: #28a745;
    }

    .status-card.model-not-ready {
        border-left-color: #ffc107;
    }

    .status-card.no-data {
        border-left-color: #dc3545;
    }

    .training-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .stat-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        border: 2px solid #e9ecef;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #667eea;
        display: block;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        margin: 20px 0;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        color: white;
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
        color: white;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .upload-area {
        border: 2px dashed #ccc;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .upload-area:hover {
        border-color: #667eea;
        background: #e3f2fd;
    }

    .upload-area.dragover {
        border-color: #667eea;
        background: #e3f2fd;
    }

    .sample-data {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        max-height: 300px;
        overflow-y: auto;
    }

    .sample-item {
        background: white;
        padding: 15px;
        margin: 10px 0;
        border-radius: 5px;
        border-left: 4px solid;
    }

    .sample-item.spam {
        border-left-color: #dc3545;
    }

    .sample-item.ham {
        border-left-color: #28a745;
    }

    .progress-bar {
        background: #f0f0f0;
        border-radius: 10px;
        height: 20px;
        margin: 15px 0;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        border-radius: 10px;
        transition: width 0.5s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.8rem;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
        border-left: 4px solid;
    }

    .alert-success {
        background: #e6ffe6;
        border-color: #28a745;
        color: #155724;
    }

    .alert-danger {
        background: #ffe6e6;
        border-color: #dc3545;
        color: #721c24;
    }

    .alert-warning {
        background: #fff3cd;
        border-color: #ffc107;
        color: #856404;
    }

    .alert-info {
        background: #e3f2fd;
        border-color: #2196f3;
        color: #0d47a1;
    }

    .training-log {
        background: #1e1e1e;
        color: #00ff00;
        font-family: 'Courier New', monospace;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        max-height: 300px;
        overflow-y: auto;
        font-size: 0.9rem;
    }

    .log-line {
        margin: 2px 0;
    }

    .log-success {
        color: #00ff00;
    }

    .log-error {
        color: #ff4444;
    }

    .log-info {
        color: #44aaff;
    }

    @media (max-width: 768px) {
        .training-container {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .training-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="training-container">
    <!-- Model Status -->
    <div class="status-card {{ $modelStatus['is_trained'] ? 'model-ready' : ($data['exists'] ? 'model-not-ready' : 'no-data') }}">
        <h3>🤖 Model Status</h3>

        @if($modelStatus['is_trained'])
            <div class="alert alert-success">
                <strong>✅ Model is Ready</strong><br>
                The spam detection model has been trained and is ready for use.
            </div>

            <div class="training-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ number_format($modelStatus['model_size'] / 1024, 1) }}</span>
                    <div class="stat-label">Model Size (KB)</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $modelStatus['last_trained'] ? date('M j, Y', strtotime($modelStatus['last_trained'])) : 'Unknown' }}</span>
                    <div class="stat-label">Last Trained</div>
                </div>
            </div>
        @elseif($data['exists'])
            <div class="alert alert-warning">
                <strong>⚠️ Model Not Trained</strong><br>
                Training data is available but the model hasn't been trained yet.
            </div>
        @else
            <div class="alert alert-danger">
                <strong>❌ No Training Data</strong><br>
                No training dataset found. Please upload training data to begin.
            </div>
        @endif
    </div>

    <!-- Training Data Status -->
    <div class="status-card {{ $data['exists'] ? 'model-ready' : 'no-data' }}">
        <h3>📊 Training Data</h3>

        @if($data['exists'])
            <div class="alert alert-info">
                <strong>📈 Data Available</strong><br>
                Training dataset contains {{ number_format($data['total_samples']) }} samples.
            </div>

            <div class="training-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ number_format($data['total_samples']) }}</span>
                    <div class="stat-label">Total Samples</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number" style="color: #dc3545;">{{ number_format($data['spam_count']) }}</span>
                    <div class="stat-label">Spam Samples</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number" style="color: #28a745;">{{ number_format($data['ham_count']) }}</span>
                    <div class="stat-label">Ham Samples</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ number_format($data['file_size'] / 1024, 1) }}</span>
                    <div class="stat-label">File Size (KB)</div>
                </div>
            </div>
        @else
            <div class="alert alert-danger">
                <strong>📁 No Data Found</strong><br>
                Training dataset is missing. Please upload a CSV file with training data.
            </div>
        @endif
    </div>
</div>

<!-- Action Buttons -->
<div class="action-buttons">
    <button class="btn btn-primary" onclick="startTraining('train')" {{ !$data['exists'] ? 'disabled' : '' }}>
        🚀 Start Training
    </button>

    <button class="btn btn-success" onclick="startTraining('retrain')" {{ !$data['exists'] ? 'disabled' : '' }}>
        🔄 Retrain Model
    </button>

    <button class="btn btn-info" onclick="checkStatus()">
        📊 Check Status
    </button>

    <button class="btn btn-warning" onclick="showMetrics()">
        📈 View Metrics
    </button>
</div>

<!-- Upload Section -->
<div class="upload-area" onclick="document.getElementById('training-file').click()">
    <h4>📤 Upload Training Data</h4>
    <p>Click to select or drag and drop your CSV file here</p>
    <p><small>Format: label<tab>message (spam/ham as labels)</small></p>
    <input type="file" id="training-file" accept=".csv,.txt" style="display: none;" onchange="uploadFile(this)">
</div>

<!-- Training Progress (Hidden by default) -->
<div id="training-progress" style="display: none;">
    <h4>🔄 Training in Progress...</h4>
    <div class="progress-bar">
        <div class="progress-fill" id="progress-fill" style="width: 0%">0%</div>
    </div>
    <div class="training-log" id="training-log">
        <div class="log-line log-info">[INFO] Initializing training process...</div>
    </div>
</div>

<!-- Sample Data Preview -->
@if($data['exists'] && count($data['sample_data']) > 0)
<div class="sample-data">
    <h4>📋 Sample Data Preview</h4>
    @foreach($data['sample_data'] as $sample)
    <div class="sample-item {{ $sample['label'] }}">
        <strong>{{ strtoupper($sample['label']) }}:</strong> {{ $sample['message'] }}
    </div>
    @endforeach
    <p><small>Showing {{ count($data['sample_data']) }} of {{ $data['total_samples'] }} total samples</small></p>
</div>
@endif

<!-- Messages -->
<div id="message-area"></div>

<!-- Data Management -->
@if($data['exists'])
<div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
    <h4>🗂️ Data Management</h4>
    <p><strong>Last Modified:</strong> {{ $data['last_modified'] }}</p>
    <button class="btn btn-danger" onclick="deleteData()">
        🗑️ Delete Training Data
    </button>
</div>
@endif

<!-- Instructions -->
<div style="margin-top: 30px; background: #e3f2fd; border-radius: 10px; padding: 20px;">
    <h4>📖 Training Instructions</h4>
    <ul>
        <li><strong>Training Data Format:</strong> CSV file with tab-separated values (label<tab>message)</li>
        <li><strong>Labels:</strong> Use "spam" for scam/fraudulent messages, "ham" for legitimate messages</li>
        <li><strong>Sample Format:</strong> <code>spam<tab>mpesa reversal ksh 2500 click link confirm pin</code></li>
        <li><strong>Minimum Data:</strong> At least 10 samples recommended for basic training</li>
        <li><strong>Best Results:</strong> 100+ samples with balanced spam/ham ratio</li>
        <li><strong>Model Updates:</strong> Retrain periodically with new data to maintain accuracy</li>
    </ul>
</div>
@endsection

@section('scripts')
<script>
let isTraining = false;

function startTraining(action) {
    if (isTraining) {
        showMessage('Training is already in progress...', 'warning');
        return;
    }

    isTraining = true;

    // Show progress section
    document.getElementById('training-progress').style.display = 'block';
    document.getElementById('training-log').innerHTML = '<div class="log-line log-info">[INFO] Starting ' + action + '...</div>';

    // Disable buttons
    document.querySelectorAll('.btn').forEach(btn => {
        if (btn.textContent.includes('Training') || btn.textContent.includes('Retrain')) {
            btn.disabled = true;
        }
    });

    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 95) progress = 95;

        updateProgress(progress);
        addLog('[INFO] Processing training data...', 'info');
    }, 1000);

    // Start actual training
    fetch('/training/train', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ action: action })
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        updateProgress(100);

        if (data.success) {
            addLog('[SUCCESS] Model training completed successfully!', 'success');
            showMessage('Training completed successfully!', 'success');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            addLog('[ERROR] Training failed: ' + data.message, 'error');
            showMessage('Training failed: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        clearInterval(progressInterval);
        addLog('[ERROR] Network error: ' + error.message, 'error');
        showMessage('Network error: ' + error.message, 'danger');
    })
    .finally(() => {
        isTraining = false;
        document.querySelectorAll('.btn').forEach(btn => {
            btn.disabled = false;
        });
    });
}

function updateProgress(percent) {
    const progressFill = document.getElementById('progress-fill');
    progressFill.style.width = percent + '%';
    progressFill.textContent = Math.round(percent) + '%';
}

function addLog(message, type) {
    const logContainer = document.getElementById('training-log');
    const logLine = document.createElement('div');
    logLine.className = 'log-line log-' + type;
    logLine.textContent = '[' + new Date().toLocaleTimeString() + '] ' + message;
    logContainer.appendChild(logLine);
    logContainer.scrollTop = logContainer.scrollHeight;
}

function uploadFile(input) {
    const file = input.files[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) { // 10MB limit
        showMessage('File too large. Maximum size is 10MB.', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('training_file', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    showMessage('Uploading training data...', 'info');

    fetch('/training/upload', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Training data uploaded successfully!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showMessage('Upload failed: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showMessage('Upload error: ' + error.message, 'danger');
    });
}

function checkStatus() {
    fetch('/training/status')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Status check completed. Check the dashboard above.', 'success');
        } else {
            showMessage('Status check failed: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showMessage('Status check error: ' + error.message, 'danger');
    });
}

function showMetrics() {
    fetch('/training/metrics')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const metrics = data.metrics;
            let message = `Training Metrics:\n` +
                         `Total Samples: ${metrics.total_samples}\n` +
                         `Spam Samples: ${metrics.spam_samples}\n` +
                         `Ham Samples: ${metrics.ham_samples}\n` +
                         `Spam Ratio: ${metrics.spam_ratio}%\n` +
                         `Ham Ratio: ${metrics.ham_ratio}%`;
            showMessage(message, 'info');
        } else {
            showMessage('Failed to get metrics: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showMessage('Metrics error: ' + error.message, 'danger');
    });
}

function deleteData() {
    if (!confirm('Are you sure you want to delete the training data? This action cannot be undone.')) {
        return;
    }

    fetch('/training/delete-data', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Training data deleted successfully!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showMessage('Delete failed: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showMessage('Delete error: ' + error.message, 'danger');
    });
}

function showMessage(message, type) {
    const messageArea = document.getElementById('message-area');
    const alertClass = 'alert-' + (type === 'warning' ? 'warning' : type);
    messageArea.innerHTML = `
        <div class="alert ${alertClass}">
            ${message}
        </div>
    `;

    // Auto-hide after 5 seconds
    setTimeout(() => {
        messageArea.innerHTML = '';
    }, 5000);
}

// Drag and drop functionality
const uploadArea = document.querySelector('.upload-area');

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('training-file').files = files;
        uploadFile(document.getElementById('training-file'));
    }
});
</script>
@endsection
