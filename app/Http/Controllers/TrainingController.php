<?php

namespace App\Http\Controllers;

use App\Services\SpamDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TrainingController extends Controller
{
    protected $detector;

    public function __construct()
    {
        $this->detector = new SpamDetector();
    }

    /**
     * Display the training interface
     */
    public function index()
    {
        $data = $this->getTrainingData();
        $modelStatus = $this->getModelStatus();

        return view('training', compact('data', 'modelStatus'));
    }

    /**
     * Start training process
     */
    public function train(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'action' => 'required|in:train,retrain'
            ]);

            $action = $request->input('action');

            // Check if training data exists
            $datasetPath = storage_path('app/spam.csv');
            if (!file_exists($datasetPath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Training dataset not found. Please upload training data first.'
                ], 404);
            }

            // Start training in background using artisan command
            $command = $action === 'retrain' ? 'train:spam-model --force' : 'train:spam-model';
            $exitCode = Artisan::call($command);

            if ($exitCode === 0) {
                // Get updated status
                $modelStatus = $this->getModelStatus();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Model training completed successfully!',
                    'data' => [
                        'modelStatus' => $modelStatus,
                        'action' => $action
                    ]
                ], 200);
            } else {
                throw new \Exception('Training command failed with exit code: ' . $exitCode);
            }
        } catch (\Exception $e) {
            Log::error('Training error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Training process failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check training status
     */
    public function status()
    {
        try {
            $modelStatus = $this->getModelStatus();
            $data = $this->getTrainingData();

            return response()->json([
                'status' => 'success',
                'message' => 'Training status retrieved successfully',
                'data' => [
                    'modelStatus' => $modelStatus,
                    'data' => $data
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Status check error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve training status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get training data statistics
     */
    public function data()
    {
        try {
            $data = $this->getTrainingData();
            return response()->json([
                'status' => 'success',
                'message' => 'Training data statistics retrieved successfully',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error('Data retrieval error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve training data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload training data
     */
    public function uploadData(Request $request)
    {
        try {
            $request->validate([
                'training_file' => 'required|file|mimes:csv,txt|max:10240' // 10MB max
            ]);

            $file = $request->file('training_file');

            // Validate CSV format
            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", trim($content));

            if (count($lines) < 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Training file must contain at least 2 lines (header + data)'
                ], 422);
            }

            // Validate first few lines for correct format
            $validLines = 0;
            foreach (array_slice($lines, 0, min(10, count($lines))) as $line) {
                if (empty(trim($line))) continue;
                $parts = explode("\t", trim($line));
                if (count($parts) >= 2 && in_array(strtolower($parts[0]), ['spam', 'ham'])) {
                    $validLines++;
                }
            }

            if ($validLines === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No valid training data found. Format should be: label\\tmessage (tab-separated)'
                ], 422);
            }

            // Backup existing file
            $existingPath = storage_path('app/spam.csv');
            if (file_exists($existingPath)) {
                $backupPath = storage_path('app/spam_backup_' . date('Y-m-d_H-i-s') . '.csv');
                copy($existingPath, $backupPath);
            }

            // Store new file
            $file->move(storage_path('app'), 'spam.csv');

            $data = $this->getTrainingData();

            return response()->json([
                'status' => 'success',
                'message' => 'Training data uploaded successfully!',
                'data' => $data
            ], 201);
        } catch (\Exception $e) {
            Log::error('Data upload error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload training data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete training data
     */
    public function deleteData()
    {
        try {
            $datasetPath = storage_path('app/spam.csv');
            if (file_exists($datasetPath)) {
                unlink($datasetPath);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Training data deleted successfully!'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Training data file not found'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Data deletion error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete training data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get model performance metrics
     */
    public function metrics()
    {
        try {
            $modelStatus = $this->getModelStatus();
            $data = $this->getTrainingData();

            // Calculate basic metrics
            $totalSamples = $data['total_samples'] ?? 0;
            $spamCount = $data['spam_count'] ?? 0;
            $hamCount = $data['ham_count'] ?? 0;

            $metrics = [
                'total_samples' => $totalSamples,
                'spam_samples' => $spamCount,
                'ham_samples' => $hamCount,
                'spam_ratio' => $totalSamples > 0 ? round(($spamCount / $totalSamples) * 100, 2) : 0,
                'ham_ratio' => $totalSamples > 0 ? round(($hamCount / $totalSamples) * 100, 2) : 0,
                'is_trained' => $modelStatus['is_trained'] ?? false,
                'model_size' => $modelStatus['model_size'] ?? 0,
                'last_trained' => $modelStatus['last_trained'] ?? null
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Model metrics retrieved successfully',
                'data' => $metrics
            ], 200);
        } catch (\Exception $e) {
            Log::error('Metrics error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve model metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get training data statistics
     */
    protected function getTrainingData(): array
    {
        $datasetPath = storage_path('app/spam.csv');
        $data = [
            'exists' => false,
            'total_samples' => 0,
            'spam_count' => 0,
            'ham_count' => 0,
            'file_size' => 0,
            'last_modified' => null,
            'sample_data' => []
        ];

        if (!file_exists($datasetPath)) {
            return $data;
        }

        $data['exists'] = true;
        $data['file_size'] = filesize($datasetPath);
        $data['last_modified'] = date('Y-m-d H:i:s', filemtime($datasetPath));

        $lines = file($datasetPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $data['total_samples'] = count($lines);

        foreach ($lines as $line) {
            $parts = explode("\t", trim($line));
            if (count($parts) >= 2) {
                $label = strtolower($parts[0]);
                $message = $parts[1];

                if ($label === 'spam') {
                    $data['spam_count']++;
                } elseif ($label === 'ham') {
                    $data['ham_count']++;
                }

                // Collect sample data (first 5 samples)
                if (count($data['sample_data']) < 5) {
                    $data['sample_data'][] = [
                        'label' => $label,
                        'message' => substr($message, 0, 100) . (strlen($message) > 100 ? '...' : '')
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Get model status information
     */
    protected function getModelStatus(): array
    {
        $modelPath = storage_path('app/spam_model.phpml');
        $status = [
            'is_trained' => false,
            'model_size' => 0,
            'last_trained' => null,
            'path' => null
        ];

        if (file_exists($modelPath)) {
            $status['is_trained'] = true;
            $status['model_size'] = filesize($modelPath);
            $status['last_trained'] = date('Y-m-d H:i:s', filemtime($modelPath));
            $status['path'] = $modelPath;
        }

        return $status;
    }
}
