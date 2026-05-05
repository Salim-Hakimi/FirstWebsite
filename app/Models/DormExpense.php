<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DormExpense extends Model
{
    protected $fillable = [
        'category',
        'title',
        'amount',
        'spent_on',
        'paid_to',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_on' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function categoryLabels(): array
    {
        return [
            'dorm_repair' => 'تعمیر لیلیه',
            'library' => 'کتاب‌خانه',
            'guard_payment' => 'مصارف گاردها',
            'other' => 'مصارف دیگر',
        ];
    }
}
