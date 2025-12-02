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

## HasConfigurableWorkflows: Implementation Guide

Use this trait when you want to toggle “Configurable Workflows” for a client/company and surface workflow choices in your forms.

What it provides:
- Automatically adds `configurable_workflows_enabled` to your model’s `fillable` on retrieval.
- `getConfigurableWorkflows()` — returns all `Workflow` records ordered by name.
- `saveConfigurableWorkflowsFlag($company, $data)` — persists the Yes/No flag from request data as an integer (0/1).

Prerequisites:
- Run the package migrations. One migration adds `configurable_workflows_enabled` to the `company` table:
  - `php artisan migrate`
- If you store the flag on a different table/model, create your own migration to add a `configurable_workflows_enabled` column there.

### 1) Add the trait to your Eloquent model

```php
use Assure\Workflow\Traits\HasConfigurableWorkflows;

class Company extends Model
{
    use HasConfigurableWorkflows;
}
```

This ensures the model can be mass-assigned with `configurable_workflows_enabled` and gives you helper methods.

### 2) Use the helpers in your controller

```php
use Assure\Workflow\Traits\HasConfigurableWorkflows;

class CompanyController extends Controller
{
    use HasConfigurableWorkflows;

    public function details($id)
    {
        $company = Company::findOrFail($id);
        $configurableWorkflows = $this->getConfigurableWorkflows();

        return view('client.detail', [
            'client' => $company,
            'configurableWorkflows' => $configurableWorkflows,
        ]);
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $data = $request->all();

        // Persist the feature flag (casts to 0/1)
        $this->saveConfigurableWorkflowsFlag($company, $data);

        return redirect()->route('client.view', ['#/view/clients']);
    }
}
```

### 3) Wire up the Blade form (toggle + dropdown)

Render the feature toggle radios (Yes/No):

```php
@include('workflow::partials.feature-toggle', ['client' => $client])
```

Label + select with configurable workflow options:

```php
<label id="pra-field-label">PRA Form Config</label>
<select name="pra_form_configuration" class="form-control">
    <!-- Your existing static options should use class="hardcoded-form" -->
    <option class="hardcoded-form" value="">-- Select a form --</option>
    <!-- ... other hardcoded options ... -->

    {{-- Configurable workflow options (auto-hidden when feature is disabled) --}}
    @include('workflow::partials.workflow-dropdown-options', [
        'workflows' => $configurableWorkflows,
        'selectedValue' => old('pra_form_configuration', $client->pra_form_configuration)
    ])
</select>
```

Drop in the small JS snippet that toggles the label and shows/hides the configurable workflows optgroup:

```php
@section('scripts')
<script>
@include('workflow::partials.label-toggle-script')
</script>
@endsection
```

Behavior:
- When the toggle is “Yes”, the label becomes “PRA Workflow” and the configurable workflows optgroup is shown while hardcoded options are hidden.
- When “No”, the label reads “PRA Form Config”, the optgroup is hidden, and hardcoded options are shown.

### 4) Notes and troubleshooting
- The trait augments `fillable` on model retrieval to include `configurable_workflows_enabled`. If you explicitly guard attributes elsewhere, ensure this field is allowed.
- `getConfigurableWorkflows()` currently returns all workflows. If you need tenant/account scoping, add that logic to your app (e.g., override via a wrapper method in your controller).
- If the optgroup never appears, verify:
  - You have at least one `Workflow` created in `/workflow`.
  - You included `label-toggle-script` in the page.
  - The select has options with class `hardcoded-form` (for proper hide/show switching).

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