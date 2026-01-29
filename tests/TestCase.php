<?php

namespace Assure\Workflow\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Assure\Workflow\WorkflowServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
//        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            WorkflowServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        
        // Setup MySQL connection for testing
        // Supports environment variables from phpunit.xml or system env
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'      => 'mysql',
            'host'        => env('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1'),
            'port'        => env('DB_PORT', getenv('DB_PORT') ?: '3306'),
            'database'    => env('DB_DATABASE', getenv('DB_DATABASE') ?: 'test'),
            'username'    => env('DB_USERNAME', getenv('DB_USERNAME') ?: 'root'),
            'password'    => env('DB_PASSWORD', getenv('DB_PASSWORD') ?: ''),
            'charset'     => 'utf8',
            'collation'   => 'utf8_unicode_ci',
            'prefix'      => '',
            'strict'      => false,
            'engine'      => null,
        ]);
    }
}

