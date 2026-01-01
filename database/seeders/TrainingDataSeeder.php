<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrainingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load data from CSV file
        $csvPath = storage_path('app/spam.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error('Training data CSV file not found at: ' . $csvPath);
            return;
        }
        
        $lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $imported = 0;
        
        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) >= 2) {
                $label = trim($parts[0]);
                $text = trim($parts[1]);
                
                if (in_array($label, ['spam', 'ham'])) {
                    \App\Models\TrainingData::create([
                        'text' => $text,
                        'label' => $label,
                        'source' => 'csv_import',
                        'is_verified' => true, // CSV data is considered verified
                    ]);
                    $imported++;
                }
            }
        }
        
        $this->command->info("Imported {$imported} training samples from CSV file.");
    }
}
