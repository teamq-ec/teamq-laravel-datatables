<?php

namespace Tests\Mocks\Mongo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Tests\Mocks\Mongo\Database\Factories\ChapterFactory;

class Chapter extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'chapters';

    protected static $unguarded = true;

    protected static function newFactory(): ChapterFactory
    {
        return ChapterFactory::new();
    }
}
