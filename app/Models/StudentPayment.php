<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPayment extends Model
{
    protected $fillable = [
        'dorm_student_id',
        'finance_transaction_id',
        'payment_month',
        'payment_year',
        'expected_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'recorded_by',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(DormStudent::class, 'dorm_student_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'finance_transaction_id')->withTrashed();
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
