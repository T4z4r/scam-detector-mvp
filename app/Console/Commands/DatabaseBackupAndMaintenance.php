<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DatabaseBackupAndMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scam:backup-maintenance 
                           {--backup : Create database backup}
                           {--cleanup : Clean up old data}
                           {--all : Perform all maintenance tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database backup and maintenance for scam detection system';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting database backup and maintenance...');

        try {
            if ($this->option('backup') || $this->option('all')) {
                $this->createDatabaseBackup();
            }

            if ($this->option('cleanup') || $this->option('all')) {
                $this->performCleanup();
            }

            $this->info('Database backup and maintenance completed successfully.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Maintenance failed: ' . $e->getMessage());
            Log::error('Database backup/maintenance failed', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }

    /**
     * Create database backup.
     */
    protected function createDatabaseBackup(): void
    {
        $this->info('Creating database backup...');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupFileName = "scam_detector_backup_{$timestamp}.sql";
        $backupPath = "backups/{$backupFileName}";

        try {
            // Create backups directory if it doesn't exist
            if (!Storage::exists('backups')) {
                Storage::makeDirectory('backups');
            }

            // Export training data
            $trainingData = DB::table('training_data')
                ->select('*')
                ->get()
                ->toArray();

            // Export user feedback
            $userFeedback = DB::table('user_feedback')
                ->select('*')
                ->get()
                ->toArray();

            // Export scam senders
            $scamSenders = DB::table('scam_senders')
                ->select('*')
                ->get()
                ->toArray();

            // Create backup data structure
            $backupData = [
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
                'tables' => [
                    'training_data' => $trainingData,
                    'user_feedback' => $userFeedback,
                    'scam_senders' => $scamSenders
                ]
            ];

            // Store backup as JSON (simpler than SQL dump)
            Storage::put($backupPath, json_encode($backupData, JSON_PRETTY_PRINT));

            $this->info("Backup created: {$backupFileName}");
            Log::info('Database backup created', ['file' => $backupPath]);

        } catch (\Exception $e) {
            throw new \Exception("Backup creation failed: " . $e->getMessage());
        }
    }

    /**
     * Perform cleanup operations.
     */
    protected function performCleanup(): void
    {
        $this->info('Performing cleanup operations...');

        // Clean up old user feedback (older than 1 year)
        $oldFeedbackCount = DB::table('user_feedback')
            ->where('created_at', '<', now()->subYear())
            ->delete();

        $this->info("Cleaned up {$oldFeedbackCount} old feedback records");

        // Clean up unverified training data (older than 30 days)
        $oldUnverifiedCount = DB::table('training_data')
            ->where('is_verified', false)
            ->where('created_at', '<', now()->subDays(30))
            ->delete();

        $this->info("Cleaned up {$oldUnverifiedCount} unverified training data records");

        // Clean up old backups (keep only last 30 days)
        $this->cleanupOldBackups();

        // Clean up old log files (keep only last 7 days)
        $this->cleanupOldLogs();

        Log::info('Database cleanup completed', [
            'old_feedback_deleted' => $oldFeedbackCount,
            'old_unverified_deleted' => $oldUnverifiedCount
        ]);
    }

    /**
     * Clean up old backup files.
     */
    protected function cleanupOldBackups(): void
    {
        $files = Storage::allFiles('backups');
        $deletedCount = 0;

        foreach ($files as $file) {
            $lastModified = Storage::lastModified($file);
            if ($lastModified < strtotime('-30 days')) {
                Storage::delete($file);
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->info("Cleaned up {$deletedCount} old backup files");
        }
    }

    /**
     * Clean up old log files.
     */
    protected function cleanupOldLogs(): void
    {
        $logFiles = glob(storage_path('logs/*.log'));
        $deletedCount = 0;

        foreach ($logFiles as $file) {
            if (filemtime($file) < strtotime('-7 days')) {
                unlink($file);
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->info("Cleaned up {$deletedCount} old log files");
        }
    }
}