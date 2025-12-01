<?php

namespace Assure\Workflow\Traits;

use Assure\Workflow\Models\Workflow;
use Illuminate\Database\Eloquent\Model;

trait HasConfigurableWorkflows
{
    /**
     * Boot the trait
     * Automatically adds configurable_workflows_enabled to fillable
     */
    public static function bootHasConfigurableWorkflows()
    {
        static::retrieved(function (Model $model) {
            // Add configurable_workflows_enabled to fillable if not already present
            $fillable = $model->getFillable();
            if (!in_array('configurable_workflows_enabled', $fillable)) {
                $fillable[] = 'configurable_workflows_enabled';
                $model->fillable($fillable);
            }
        });
    }
    /**
     * Get all configurable workflows ordered by name
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConfigurableWorkflows()
    {
        return Workflow::orderBy('name')->get();
    }

    /**
     * Save configurable workflows enabled flag
     * Ensures consistent integer data type (0 or 1)
     *
     * @param \App\Models\Company $company
     * @param array $data
     * @return void
     */
    public function saveConfigurableWorkflowsFlag($company, $data)
    {
        // Cast to int to ensure consistent data type (0 or 1)
        $company->configurable_workflows_enabled = (int) (\idx($data, 'configurable_workflows_enabled') ?? 0);
    }
}