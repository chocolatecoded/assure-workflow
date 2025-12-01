<?php

namespace Assure\Workflow;

use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/workflow.php' => config_path('workflow.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'workflow');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        // Serve assets directly from the package; no publish to public
        \Route::group(['middleware' => ['web']], function () {
            \Route::get('/vendor/assure-workflow/{path}', function ($path) {
                $file = __DIR__ . '/../public/' . $path;
                if (!file_exists($file)) {
                    abort(404);
                }
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'js' => 'application/javascript',
                    'css' => 'text/css',
                    'woff' => 'font/woff',
                    'woff2' => 'font/woff2',
                    'ttf' => 'font/ttf',
                    'eot' => 'application/vnd.ms-fontobject',
                    'svg' => 'image/svg+xml'
                ];
                $mime = $mimeTypes[$extension] ?? null;
                return response()->file($file, $mime ? ['Content-Type' => $mime] : []);
            })->where('path', '.*');
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/workflow.php', 'workflow');
        $this->app->singleton(Services\WorkflowEngine::class, function ($app) {
            return new Services\WorkflowEngine(
                new Services\ConfigurationManager(config('workflow'))
            );
        });
    }
}

