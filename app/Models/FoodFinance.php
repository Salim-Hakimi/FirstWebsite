<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodFinance extends Model
{
    protected $fillable = [
        'dorm_student_id',
        'recorded_by',
        'type',
        'amount',
        'recorded_at',
        'period',
        'vendor_or_source',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'date',
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
