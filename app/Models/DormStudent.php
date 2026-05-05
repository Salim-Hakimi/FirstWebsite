<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DormStudent extends Model
{
    protected $fillable = [
        'dorm_room_id',
        'registered_by',
        'full_name',
        'father_name',
        'phone',
        'whatsapp',
        'email',
        'tazkira_number',
        'education_place',
        'department_or_grade',
        'province',
        'room_number',
        'bed_number',
        'guarantor_name',
        'guarantor_phone',
        'document_names',
        'profile_photo_path',
        'application_date',
        'education_score',
        'eligibility_score',
        'eligibility_notes',
        'admitted_at',
        'admission_decision_by',
        'status',
        'joined_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'document_names' => 'array',
            'application_date' => 'date',
            'education_score' => 'decimal:2',
            'joined_at' => 'date',
            'admitted_at' => 'datetime',
        ];
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function admissionDecisionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admission_decision_by');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(DormRoom::class, 'dorm_room_id');
    }

    public function collections(): HasMany
    {
        return $this->hasMany(StudentCollection::class);
    }

    public function foodFinances(): HasMany
    {
        return $this->hasMany(FoodFinance::class);
    }

    public function membershipCards(): MorphMany
    {
        return $this->morphMany(MembershipCard::class, 'cardable');
    }

    public function latestDormCard()
    {
        return $this->membershipCards()->where('scope', 'dorm')->latest('expires_at')->first();
    }
}
