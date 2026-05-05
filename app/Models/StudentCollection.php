<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCollection extends Model
{
    protected $fillable = [
        'dorm_student_id',
        'recorded_by',
        'type',
        'amount',
        'collected_at',
        'period',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'collected_at' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(DormStudent::class, 'dorm_student_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
