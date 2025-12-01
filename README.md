# Assure Workflow (Laravel 5.5 Package)

A modular workflow package providing models, controllers, Vue-powered CRUD UI, and migrations for workflows, steps, and instances.

## Requirements
- PHP 7.0+ (Laravel 5.5 app)
- Node.js (to build assets)

## Install (Laravel 5.5 app)

### One-step install

```
php artisan assure-workflow:install --build
```

Options:
- `--production` to run a production asset build (`npm run production`)
- Omit `--build` if you only want to run the DB migrations

This command:
- Runs package migrations (auto-loaded via the service provider)
- Optionally installs Node deps and builds the package assets

Assets are served directly from the package at `/vendor/assure-workflow/*`, so publishing to `public/` is not required.

### Manual install (if you prefer explicit steps)
1) Add PSR-4 autoload to your app `composer.json` (local path example):

```
"autoload": {
  "psr-4": {
    "Assure\\Workflow\\": "packages/workflow/src/"
  }
}
```

Then run:

```
composer dump-autoload
```

2) Service Provider

The package registers via Laravel package auto-discovery. If you disabled discovery, register the provider in `config/app.php`:

```
'providers' => [
  // ...
  Assure\\Workflow\\WorkflowServiceProvider::class,
],
```

3) Build assets (optional)

```
cd packages/workflow
npm install
npm run dev
```

Assets are served from `/vendor/assure-workflow/*` by the package; publishing to `public/` is not required.

4) Run migrations:

Ensure your DB is reachable (.env), then:

```
php artisan migrate
```

The package auto-loads its migrations from `packages/workflow/database/migrations`.

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
  - PHP: `packages/workflow/src` (models, services, controllers, provider)
  - Vue/Assets: `packages/workflow/resources/assets` (built via Laravel Mix)
  - Views: `packages/workflow/resources/views`
- Build:

```
npm run dev     # dev build
npm run watch   # watch mode
npm run production  # minified build
```

## Notes
- Package ships Bootstrap 4, jQuery, and Popper bundled locally for offline/fast loads.
- If you change DB settings, run `php artisan config:clear` to pick up new .env.