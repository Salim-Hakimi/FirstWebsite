<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceProject extends Model
{
    protected $fillable = [
        'name',
        'category',
        'estimated_budget',
        'status',
        'started_on',
        'completed_on',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'completed_on' => 'date',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }
}
