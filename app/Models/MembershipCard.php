<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MembershipCard extends Model
{
    protected $fillable = [
        'cardable_type',
        'cardable_id',
        'scope',
        'card_number',
        'holder_name',
        'father_name',
        'issued_at',
        'expires_at',
        'fee_amount',
        'payment_status',
        'paid_at',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'fee_amount' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public function cardable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }

    public function paymentIsDueToday(): bool
    {
        return $this->expires_at?->isToday() && $this->payment_status !== 'paid';
    }
}
