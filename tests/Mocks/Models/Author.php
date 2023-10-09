<?php

namespace Tests\Mocks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Mocks\Database\Factories\AuthorFactory;
use Tests\Mocks\Enums\AuthorTypeEnum;

class Author extends Model
{
    use HasFactory;

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
}
