<?php

namespace Tests;

use Kirschbaum\PowerJoins\PowerJoinsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use TeamQ\QueryBuilder\QueryBuilderServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/Mocks/Database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Spatie\QueryBuilder\QueryBuilderServiceProvider::class,
            QueryBuilderServiceProvider::class,
            PowerJoinsServiceProvider::class,
        ];
    }
}
