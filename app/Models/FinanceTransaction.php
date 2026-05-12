<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceTransaction extends Model
{
    use SoftDeletes;

    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    protected $fillable = [
        'transaction_number',
        'type',
        'finance_category_id',
        'dorm_student_id',
        'recorded_by',
        'amount',
        'expected_amount',
        'transaction_date',
        'period',
        'payment_method',
        'payment_status',
        'payer_name',
        'payee_name',
        'donor_name',
        'donor_phone',
        'project_name',
        'attachment_path',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'finance_category_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(DormStudent::class, 'dorm_student_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getBalanceAttribute(): int
    {
        return max(0, (int) $this->expected_amount - (int) $this->amount);
    }
}
