<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DormRoom extends Model
{
    protected $fillable = [
        'room_number',
        'building',
        'capacity',
        'floor',
        'status',
        'notes',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(DormStudent::class);
    }

    public function activeStudents(): HasMany
    {
        return $this->students()->where('status', 'active');
    }

    public function availableBeds(): int
    {
        return max(0, $this->capacity - $this->activeStudents()->count());
    }
}
