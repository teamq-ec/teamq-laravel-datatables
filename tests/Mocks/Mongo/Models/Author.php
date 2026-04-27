<?php

namespace Tests\Mocks\Mongo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;
use Tests\Mocks\Enums\AuthorTypeEnum;
use Tests\Mocks\Mongo\Database\Factories\AuthorFactory;

class Author extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'authors';

    protected static $unguarded = true;

    protected $casts = [
        'type' => AuthorTypeEnum::class,
    ];

    protected static function newFactory(): AuthorFactory
    {
        return AuthorFactory::new();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
