<?php

namespace Assure\Workflow\Traits;

use Assure\Workflow\Models\WorkflowInstance;

trait HasWorkflow
{
    public function workflowInstances()
    {
        return $this->morphMany(WorkflowInstance::class, 'subject');
    }
}

