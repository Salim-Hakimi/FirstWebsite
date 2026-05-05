<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'registered_by',
        'isbn',
        'title',
        'author',
        'publisher',
        'language',
        'edition',
        'published_year',
        'pages',
        'category',
        'shelf_code',
        'barcode',
        'total_copies',
        'available_copies',
        'status',
        'notes',
    ];

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }
}
