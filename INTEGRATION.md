# Integration Guide for Main Application

This guide helps you integrate the Assure Workflow package into your Laravel application after the asset publishing refactor.

## Quick Start

### 1. Install the Package

```bash
composer require assure/workflow
```

### 2. Publish Assets

```bash
# Publish source assets (JS/SASS)
php artisan vendor:publish --tag=workflow-assets

# Publish static assets (fonts)
php artisan vendor:publish --tag=workflow-fonts

# Optional: Publish config
php artisan vendor:publish --tag=workflow-config
```

### 3. Configure Webpack Mix

Add to your `webpack.mix.js`:

```js
// Compile workflow package assets
mix.js('resources/assets/vendor/workflow/js/workflow.js', 'public/vendor/workflow/js')
   .sass('resources/assets/vendor/workflow/sass/workflow.scss', 'public/vendor/workflow/css');
```

### 4. Compile Assets

```bash
npm run dev
# or for production
npm run production
```

### 5. Run Migrations

```bash
php artisan migrate
```

## Asset Paths

After compilation, assets will be available at:
- JavaScript: `/vendor/assure-workflow/js/workflow.js`
- CSS: `/vendor/assure-workflow/css/workflow.css`
- Fonts: `/vendor/assure-workflow/fonts/*`

The package's service provider automatically serves these from `public/vendor/workflow/`.

## Updating the Package

When pulling new changes from the workflow package:

```bash
# Update the package
composer update assure/workflow

# Republish source assets (use --force to overwrite)
php artisan vendor:publish --tag=workflow-assets --force

# Recompile
npm run dev
```

## Development with Local Package

To develop with a local copy of the package:

### Via Path Repository

In your main app's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../assure-workflow"
    }
  ],
  "require": {
    "assure/workflow": "@dev"
  }
}
```

Then:

```bash
composer update assure/workflow
php artisan vendor:publish --tag=workflow-assets --force
npm run dev
```

### Via Symlink (Alternative)

```bash
# Remove the vendor copy
rm -rf vendor/assure/workflow

# Symlink your local development copy
ln -s /path/to/your/local/assure-workflow vendor/assure/workflow

# Publish and compile
php artisan vendor:publish --tag=workflow-assets --force
npm run dev
```

## Troubleshooting

### Assets not loading

1. Check that assets were published:
   ```bash
   ls -la resources/assets/vendor/workflow/
   ```

2. Check that assets were compiled:
   ```bash
   ls -la public/vendor/workflow/js/
   ls -la public/vendor/workflow/css/
   ```

3. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

### Changes not reflecting

1. Republish assets with `--force`:
   ```bash
   php artisan vendor:publish --tag=workflow-assets --force
   ```

2. Recompile:
   ```bash
   npm run dev
   ```

3. Hard refresh browser (Ctrl+Shift+R / Cmd+Shift+R)

### Webpack errors

If you get webpack errors about missing dependencies, the package's dependencies might not be in your main app's `package.json`. Add them:

```json
{
  "devDependencies": {
    "axios": "^0.21.4",
    "bootstrap": "^4.6.2",
    "bootstrap-vue": "^2.23.1",
    "jquery": "^3.6.4",
    "popper.js": "^1.16.1",
    "sweetalert2": "^11.10.0",
    "vue": "^2.6.14"
  },
  "dependencies": {
    "vuedraggable": "^2.24.3"
  }
}
```

Then run:
```bash
npm install
npm run dev
```

## Production Deployment

For production deployments, add these steps to your deployment script:

```bash
composer install --no-dev --optimize-autoloader
php artisan vendor:publish --tag=workflow-assets --force
php artisan vendor:publish --tag=workflow-fonts
npm install --production
npm run production
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
