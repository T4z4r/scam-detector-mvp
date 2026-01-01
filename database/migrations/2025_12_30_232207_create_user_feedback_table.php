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
        Schema::create('user_feedback', function (Blueprint $table) {
            $table->id();
            $table->text('message_text'); // The message that was reported
            $table->string('sender')->nullable(); // The sender of the message
            $table->enum('feedback_type', ['scam_message', 'scam_sender', 'false_positive', 'false_negative']);
            $table->ipAddress('user_ip')->nullable(); // IP address of reporter (for analytics)
            $table->text('user_agent')->nullable(); // User agent for device tracking
            $table->string('original_prediction')->nullable(); // What the model predicted
            $table->float('original_confidence')->nullable(); // Model's confidence score
            $table->boolean('is_processed')->default(false); // Whether this feedback has been processed
            $table->timestamps();
            
            // Indexes
            $table->index(['feedback_type', 'is_processed']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};
