<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainingData extends Model
{
    use HasFactory;

    protected $table = 'training_data';

    protected $fillable = [
        'text',
        'label',
        'source',
        'confidence_score',
        'is_verified',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'confidence_score' => 'float',
    ];

    /**
     * Scope to get only spam samples
     */
    public function scopeSpam($query)
    {
        return $query->where('label', 'spam');
    }

    /**
     * Scope to get only ham samples
     */
    public function scopeHam($query)
    {
        return $query->where('label', 'ham');
    }

    /**
     * Scope to get verified samples only
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Get samples suitable for training
     */
    public function scopeForTraining($query)
    {
        return $query->where('is_verified', true)
                    ->orWhere('confidence_score', '>=', 0.8);
    }
}