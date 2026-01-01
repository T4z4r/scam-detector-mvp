<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFeedback extends Model
{
    use HasFactory;

    protected $table = 'user_feedback';

    protected $fillable = [
        'message_text',
        'sender',
        'feedback_type',
        'user_ip',
        'user_agent',
        'original_prediction',
        'original_confidence',
        'is_processed',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_processed' => 'boolean',
        'original_confidence' => 'float',
    ];

    /**
     * Feedback types
     */
    const FEEDBACK_TYPE_SCAM_MESSAGE = 'scam_message';
    const FEEDBACK_TYPE_SCAM_SENDER = 'scam_sender';
    const FEEDBACK_TYPE_False_POSITIVE = 'false_positive';
    const FEEDBACK_TYPE_False_NEGATIVE = 'false_negative';

    /**
     * Scope to get unprocessed feedback
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    /**
     * Scope to get scam message reports
     */
    public function scopeScamMessages($query)
    {
        return $query->where('feedback_type', self::FEEDBACK_TYPE_SCAM_MESSAGE);
    }

    /**
     * Scope to get scam sender reports
     */
    public function scopeScamSenders($query)
    {
        return $query->where('feedback_type', self::FEEDBACK_TYPE_SCAM_SENDER);
    }

    /**
     * Get display name for feedback type
     */
    public function getFeedbackTypeNameAttribute()
    {
        return match($this->feedback_type) {
            self::FEEDBACK_TYPE_SCAM_MESSAGE => 'Scam Message',
            self::FEEDBACK_TYPE_SCAM_SENDER => 'Scam Sender',
            self::FEEDBACK_TYPE_False_POSITIVE => 'False Positive',
            self::FEEDBACK_TYPE_False_NEGATIVE => 'False Negative',
            default => 'Unknown'
        };
    }
}