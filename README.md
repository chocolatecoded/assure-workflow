# Assure Workflow (Laravel 5.5 Package)

A modular workflow package providing models, controllers, Vue-powered CRUD UI, and migrations for workflows, steps, and instances.

## Requirements
- PHP 7.0+ (Laravel 5.5 app)
- Node.js (to build assets)

## Install (local package within an existing Laravel 5.5 app)
1) Add PSR-4 autoload to your app `composer.json`:

```
"autoload": {
  "psr-4": {
    "Assure\\Workflow\\": "workflow-package/src/"
  }
}
```

Then run:

```
composer dump-autoload
```

2) Register the service provider in `config/app.php`:

```
'providers' => [
  // ...
  Assure\\Workflow\\WorkflowServiceProvider::class,
],
```

3) Build and publish assets:

```
cd workflow-package
npm install
npm run dev
php artisan vendor:publish --provider="Assure\\Workflow\\WorkflowServiceProvider" --tag=public --force
```

This publishes compiled CSS/JS to `public/vendor/assure-workflow/`.

4) Run migrations:

Ensure your DB is reachable (.env), then:

```
php artisan migrate
```

The package auto-loads its migrations from `workflow-package/database/migrations`.

## Routes
- Web UI
  - `GET /workflow` (index)
  - `POST /workflow/{id}/start` (start instance)
  - `GET /workflow/instances/{id}` (show instance)
- API
  - `GET /api/workflow` (list)
  - `POST /api/workflow` (create)
  - `PUT /api/workflow/{id}` (update)
  - `DELETE /api/workflow/{id}` (delete)

## Usage
- Visit `/workflow` to manage Workflows.
- Use the “Add Workflow” button to create new workflows.
- Inline edit name/description; Save or Delete per row.

## Development
- Source:
  - PHP: `workflow-package/src` (models, services, controllers, provider)
  - Vue/Assets: `workflow-package/resources/assets` (built to `workflow-package/public` via Laravel Mix)
  - Views: `workflow-package/resources/views`
- Build:

```
npm run dev     # dev build
npm run watch   # watch mode
npm run production  # minified build
```

## Publishing
Re-publish assets after each build if your app serves from `public/vendor`:

```
php artisan vendor:publish --provider="Assure\\Workflow\\WorkflowServiceProvider" --tag=public --force
```

## Notes
- Package ships Bootstrap 4, jQuery, and Popper bundled locally for offline/fast loads.
- If you change DB settings, run `php artisan config:clear` to pick up new .env.