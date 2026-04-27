<?php

namespace Tests;

use MongoDB\Laravel\MongoDBServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use TeamQ\Datatables\DatatablesServiceProvider;

class MongoTestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshMongoDatabase();
    }

    protected function refreshMongoDatabase(): void
    {
        $database = $this->app['db']->connection('mongodb')->getMongoDB();

        foreach ($database->listCollectionNames() as $collection) {
            $database->dropCollection($collection);
        }
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'mongodb');
        $app['config']->set('database.connections.mongodb', [
            'driver' => 'mongodb',
            'host' => env('MONGO_HOST', '127.0.0.1'),
            'port' => (int) env('MONGO_PORT', 27017),
            'database' => env('MONGO_DATABASE', 'laravel_datatables_testing'),
            'username' => env('MONGO_USERNAME') ?: null,
            'password' => env('MONGO_PASSWORD') ?: null,
            'options' => array_filter([
                'authSource' => env('MONGO_AUTH_SOURCE') ?: null,
            ]),
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            QueryBuilderServiceProvider::class,
            DatatablesServiceProvider::class,
            MongoDBServiceProvider::class,
        ];
    }
}
