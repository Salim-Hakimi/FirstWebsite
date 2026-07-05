<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LibraryMember extends Model
{
    protected $fillable = [
        'registered_by',
        'member_code',
        'full_name',
        'father_name',
        'phone',
        'email',
        'tazkira_number',
        'education_place',
        'department_or_grade',
        'address',
        'profile_photo_path',
        'membership_fee',
        'card_fee_amount',
        'monthly_fee_daily_fine',
        'monthly_fee_fine_amount',
        'payment_status',
        'last_paid_at',
        'next_payment_due_at',
        'last_fee_reminder_at',
        'joined_at',
        'left_at',
        'membership_expires_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'left_at' => 'date',
            'membership_expires_at' => 'date',
            'last_paid_at' => 'date',
            'next_payment_due_at' => 'date',
            'last_fee_reminder_at' => 'date',
        ];
    }

    public function overdueFeeDays(): int
    {
        if (! $this->next_payment_due_at) {
            return 0;
        }

        return max(0, $this->next_payment_due_at->diffInDays(today(), false));
    }

    public function calculatedMonthlyFine(): int
    {
        return $this->overdueFeeDays() * (int) ($this->monthly_fee_daily_fine ?? 20);
    }

    public function monthlyFeeBalance(): int
    {
        return (int) $this->membership_fee + max((int) $this->monthly_fee_fine_amount, $this->calculatedMonthlyFine());
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }

    public function membershipCards(): MorphMany
    {
        return $this->morphMany(MembershipCard::class, 'cardable');
    }

    public function latestLibraryCard()
    {
        return $this->membershipCards()->where('scope', 'library')->latest('expires_at')->first();
    }
}
