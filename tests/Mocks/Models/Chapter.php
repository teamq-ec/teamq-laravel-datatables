<?php

namespace Tests\Mocks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tests\Mocks\Database\Factories\ChapterFactory;

class Chapter extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    protected static function newFactory(): ChapterFactory
    {
        return ChapterFactory::new();
    }
}
