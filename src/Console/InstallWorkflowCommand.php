<?php

namespace Assure\Workflow\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class InstallWorkflowCommand extends Command
{
    protected $signature = 'assure-workflow:install {--build : Build package assets with npm} {--production : Use npm run production instead of dev}';
    protected $description = 'Install Assure Workflow: run migrations and optionally build assets';

    public function handle(): int
    {
        $this->info('Assure Workflow: installation started');

        // 1) Ensure migrations are run (package uses loadMigrationsFrom)
        $this->line('Running migrations...');
        $this->call('migrate', ['--force' => true]);

        // 2) Optionally build assets within the package directory
        if ($this->option('build')) {
            $packagePath = realpath(__DIR__ . '/../../');
            if (!$packagePath) {
                $this->error('Unable to resolve package path for asset build.');
                return 1;
            }
            $this->line('Building package assets (npm)...');
            $useProduction = $this->option('production');

            // Prefer npm ci if lock exists
            $npmInstallCmd = file_exists($packagePath . '/package-lock.json') ? ['npm', 'ci'] : ['npm', 'install'];
            $install = new Process($npmInstallCmd, $packagePath);
            $install->setTimeout(null);
            $install->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });
            if (!$install->isSuccessful()) {
                $this->error('npm install failed.');
                return 1;
            }

            $buildCmd = $useProduction ? ['npm', 'run', 'production'] : ['npm', 'run', 'dev'];
            $build = new Process($buildCmd, $packagePath);
            $build->setTimeout(null);
            $build->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });
            if (!$build->isSuccessful()) {
                $this->error('npm build failed.');
                return 1;
            }
        } else {
            $this->line('Skipping asset build (use --build to enable).');
        }

        $this->info('Assure Workflow: installation completed');
        return 0;
    }
}


