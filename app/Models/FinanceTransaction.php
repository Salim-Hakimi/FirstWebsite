<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_number',
        'type',
        'finance_category_id',
        'finance_donor_id',
        'finance_project_id',
        'dorm_student_id',
        'expected_amount',
        'amount',
        'transaction_date',
        'source_or_payee',
        'payer_name',
        'payee_name',
        'receipt_number',
        'payment_method',
        'status',
        'notes',
        'description',
        'attachment_required',
        'payment_month',
        'payment_year',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'attachment_required' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'finance_category_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(FinanceProject::class, 'finance_project_id');
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(FinanceDonor::class, 'finance_donor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(DormStudent::class, 'dorm_student_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FinanceAttachment::class);
    }

    public function studentPayment(): HasOne
    {
        return $this->hasOne(StudentPayment::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(FinanceAuditLog::class);
    }

    public function displayPerson(): string
    {
        return $this->donor?->name
            ?: ($this->project?->name
                ?: ($this->source_or_payee ?: $this->payer_name ?: $this->payee_name ?: 'عمومی'));
    }

    public function isMissingAttachment(): bool
    {
        return $this->attachment_required && $this->attachments->isEmpty();
    }

    public function remainingAmount(): int
    {
        $expected = $this->expected_amount ?? $this->amount;

        return max(0, (int) $expected - (int) $this->amount);
    }
}
