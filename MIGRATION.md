# Asset Management Refactor - Summary

## Changes Made

### 1. Updated `.gitignore`
Added compiled assets to gitignore:
- `public/js/`
- `public/css/`
- `public/mix-manifest.json`
- Static assets (fonts) are still tracked

### 2. Updated `WorkflowServiceProvider.php`
Added multiple publish tags for better organization:

- **`workflow-config`**: Publishes config file
- **`workflow-assets`**: Publishes source JS/SASS files to main app's `resources/assets/vendor/workflow/`
- **`workflow-fonts`**: Publishes static font files to main app's `public/vendor/workflow/fonts/`
- **`workflow-webpack`**: Publishes webpack config example

Updated asset serving logic to check main app's `public/vendor/workflow/` first, then fallback to package directory.

### 3. Updated `README.md`
- Removed outdated `assure-workflow:install` command references
- Added clear setup steps for asset publishing and compilation
- Updated development guide to reflect new workflow
- Added warnings about not committing compiled assets

### 4. Created `INTEGRATION.md`
Comprehensive guide for main app developers covering:
- Quick start steps
- Webpack configuration
- Development workflows
- Troubleshooting
- Production deployment

## Why These Changes?

### Problems Solved
1. ❌ **Hardcoded paths**: The 111K line `workflow.js` contained absolute paths like `/home/iamgroot/Documents/projects/swap/assure-workflow`
2. ❌ **Not portable**: Different developers had different paths in compiled files
3. ❌ **Merge conflicts**: Compiled files created conflicts and unreadable diffs
4. ❌ **Bloated repo**: 112K lines of compiled code in version control

### Benefits
1. ✅ **Clean git history**: Only source files tracked
2. ✅ **No path leaks**: Compilation happens in consuming app's context
3. ✅ **Smaller repo**: ~99% reduction in tracked lines
4. ✅ **Standard Laravel pattern**: Follows Laravel package best practices
5. ✅ **Flexible**: Main app controls compilation settings

## Next Steps

### To Apply These Changes

1. **Remove currently tracked compiled files from git:**
   ```bash
   git rm --cached public/js/workflow.js
   git rm --cached public/css/workflow.css
   git rm --cached public/mix-manifest.json
   ```

2. **Commit the changes:**
   ```bash
   git add .gitignore src/WorkflowServiceProvider.php README.md INTEGRATION.md
   git commit -m "Refactor: Publish source assets instead of compiled bundles

   - Add compiled assets to .gitignore to prevent path leaks
   - Update ServiceProvider to publish source files for compilation
   - Add comprehensive integration guide for main app
   - Remove hardcoded paths from compiled bundles"
   ```

3. **Push the changes:**
   ```bash
   git push -u origin TKF-1963-mobile-api
   ```

### For Main App Integration

In your main application:

1. **Update composer to use the new branch:**
   ```json
   "assure/workflow": "dev-TKF-1963-mobile-api"
   ```

2. **Update and publish:**
   ```bash
   composer update assure/workflow
   php artisan vendor:publish --tag=workflow-assets
   php artisan vendor:publish --tag=workflow-fonts
   ```

3. **Add to `webpack.mix.js`:**
   ```js
   mix.js('resources/assets/vendor/workflow/js/workflow.js', 'public/vendor/workflow/js')
      .sass('resources/assets/vendor/workflow/sass/workflow.scss', 'public/vendor/workflow/css');
   ```

4. **Compile:**
   ```bash
   npm run dev
   ```

## Migration for Existing Installations

For teams already using this package, they'll need to:

1. Pull the latest package updates
2. Run the publish commands
3. Update their webpack.mix.js
4. Compile assets
5. Deploy

See `INTEGRATION.md` for detailed steps.

## Files Changed
- `.gitignore` - Added compiled asset exclusions
- `src/WorkflowServiceProvider.php` - Added asset publishing logic
- `README.md` - Updated documentation
- `INTEGRATION.md` - New integration guide
- `MIGRATION.md` - This file

## Files to Remove from Git
- `public/js/workflow.js` (111,937 lines)
- `public/css/workflow.css`
- `public/mix-manifest.json`

## Files to Keep
- `public/fonts/*` - Static assets are fine to track
- `resources/assets/**` - All source files
- All PHP, migration, view files
