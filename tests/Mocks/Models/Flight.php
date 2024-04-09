<?php

namespace Tests\Mocks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Mocks\Database\Factories\FlightFactory;

class Flight extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    protected static function newFactory(): FlightFactory
    {
        return FlightFactory::new();
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function arrival(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
