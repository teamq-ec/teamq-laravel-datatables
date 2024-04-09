<?php

namespace Tests\Mocks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\Mocks\Database\Factories\CountryFactory;

class Country extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    protected static function newFactory(): CountryFactory
    {
        return CountryFactory::new();
    }

    public function authors(): HasMany
    {
        return $this->hasMany(Author::class);
    }
}
