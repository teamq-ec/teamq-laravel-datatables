<?php

namespace Tests\Mocks\Mongo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Tests\Mocks\Enums\BookClassificationEnum;
use Tests\Mocks\Mongo\Database\Factories\BookFactory;

class Book extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'books';

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
