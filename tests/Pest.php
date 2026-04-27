<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\MongoTestCase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Filters', 'Sorts', 'Queries');

uses(MongoTestCase::class)->in('Mongo');
