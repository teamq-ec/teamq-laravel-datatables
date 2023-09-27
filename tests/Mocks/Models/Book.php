<?php

namespace Tests\Mocks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\Mocks\Database\Factories\BookFactory;
use Tests\Mocks\Enums\BookClassificationEnum;

class Book extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    protected $casts = [
        'classification' => BookClassificationEnum::class,
    ];

    protected static function newFactory(): BookFactory
    {
        return BookFactory::new();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }
}
