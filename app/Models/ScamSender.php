<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScamSender extends Model
{
    use HasFactory;

    protected $table = 'scam_senders';

    protected $fillable = [
        'sender_identifier',
        'sender_type',
        'report_count',
        'is_confirmed',
        'first_reported_at',
        'last_reported_at',
        'source',
        'notes',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'report_count' => 'integer',
        'first_reported_at' => 'datetime',
        'last_reported_at' => 'datetime',
    ];

    /**
     * Sender types
     */
    const SENDER_TYPE_PHONE = 'phone';
    const SENDER_TYPE_EMAIL = 'email';
    const SENDER_TYPE_NAME = 'name';
    const SENDER_TYPE_SHORT_CODE = 'short_code';

    /**
     * Scope to get confirmed scammers
     */
    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }

    /**
     * Scope to get high-risk senders (multiple reports)
     */
    public function scopeHighRisk($query)
    {
        return $query->where('report_count', '>=', 3);
    }

    /**
     * Scope to get by sender type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('sender_type', $type);
    }

    /**
     * Get display name for sender type
     */
    public function getSenderTypeNameAttribute()
    {
        return match($this->sender_type) {
            self::SENDER_TYPE_PHONE => 'Phone Number',
            self::SENDER_TYPE_EMAIL => 'Email Address',
            self::SENDER_TYPE_NAME => 'Name/Identifier',
            self::SENDER_TYPE_SHORT_CODE => 'Short Code',
            default => 'Unknown'
        };
    }

    /**
     * Check if sender is high risk
     */
    public function getIsHighRiskAttribute()
    {
        return $this->report_count >= 3 || $this->is_confirmed;
    }
}