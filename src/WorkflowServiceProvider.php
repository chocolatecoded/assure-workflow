<?php

namespace Assure\Workflow;

use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallWorkflowCommand::class,
            ]);
        }

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/workflow.php' => config_path('workflow.php'),
        ], 'workflow-config');

        // Publish source assets for compilation in main app
        $this->publishes([
            __DIR__ . '/../resources/assets/js' => resource_path('assets/vendor/workflow/js'),
            __DIR__ . '/../resources/assets/sass' => resource_path('assets/vendor/workflow/sass'),
        ], 'workflow-assets');

        // Publish static assets (fonts)
        $this->publishes([
            __DIR__ . '/../public/fonts' => public_path('vendor/workflow/fonts'),
        ], 'workflow-fonts');

        // Publish webpack mix configuration example
        $this->publishes([
            __DIR__ . '/../webpack.mix.js' => base_path('webpack.mix.workflow.js'),
        ], 'workflow-webpack');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'workflow');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        
        // Serve assets: Check main app's public directory first, fallback to package
        \Route::group(['middleware' => ['web']], function () {
            \Route::get('/vendor/assure-workflow/{path}', function ($path) {
                // Try main app's public directory first (for compiled assets)
                $publicFile = public_path('vendor/workflow/' . $path);
                if (file_exists($publicFile)) {
                    $file = $publicFile;
                } else {
                    // Fallback to package directory (for fonts and other static assets)
                    $file = __DIR__ . '/../public/' . $path;
                    if (!file_exists($file)) {
                        abort(404);
                    }
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

