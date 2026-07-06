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
        'payment_status',
        'paid_at',
        'card_printed',
        'printed_at',
        'replacement_reason',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'paid_at' => 'date',
            'card_printed' => 'boolean',
            'printed_at' => 'datetime',
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

    public function isLibraryCard(): bool
    {
        return $this->scope === 'library';
    }

    public function isActive(): bool
    {
        return ! $this->isExpired();
    }
}
