<?php

namespace Tests\Mocks\Mongo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;
use Tests\Mocks\Mongo\Database\Factories\CountryFactory;

class Country extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'countries';

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
