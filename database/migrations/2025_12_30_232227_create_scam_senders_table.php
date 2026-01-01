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
        Schema::create('scam_senders', function (Blueprint $table) {
            $table->id();
            $table->string('sender_identifier'); // Phone number, email, name, etc.
            $table->enum('sender_type', ['phone', 'email', 'name', 'short_code']);
            $table->integer('report_count')->default(1); // Number of times reported
            $table->boolean('is_confirmed')->default(false); // Whether this sender is confirmed as scam
            $table->timestamp('first_reported_at')->nullable();
            $table->timestamp('last_reported_at')->nullable();
            $table->string('source')->default('user_report'); // Where this info came from
            $table->text('notes')->nullable(); // Additional notes about this sender
            $table->timestamps();
            
            // Unique constraint on sender_identifier + sender_type
            $table->unique(['sender_identifier', 'sender_type']);
            
            // Indexes
            $table->index(['is_confirmed', 'report_count']);
            $table->index('sender_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scam_senders');
    }
};
