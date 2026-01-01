<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('training_data', function (Blueprint $table) {
            $table->id();
            $table->text('text'); // The message content
            $table->enum('label', ['spam', 'ham']); // Classification label
            $table->string('source')->default('manual'); // Where this data came from
            $table->float('confidence_score')->nullable(); // Confidence if from ML prediction
            $table->boolean('is_verified')->default(false); // Whether this sample is verified
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['label', 'is_verified']);
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_data');
    }
};
