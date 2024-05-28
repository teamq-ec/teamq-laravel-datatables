<?php

namespace Tests;

use Kirschbaum\PowerJoins\PowerJoinsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use TeamQ\Datatables\DatatablesServiceProvider;

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
            QueryBuilderServiceProvider::class,
            DatatablesServiceProvider::class,
            PowerJoinsServiceProvider::class,
        ];
    }
}
