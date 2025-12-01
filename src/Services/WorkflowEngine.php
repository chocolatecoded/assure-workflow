<?php

namespace Assure\Workflow\Services;

use Assure\Workflow\Models\Workflow;
use Assure\Workflow\Models\WorkflowStep;
use Assure\Workflow\Models\WorkflowInstance;

class WorkflowEngine
{
    private $config;

    public function __construct(ConfigurationManager $config)
    {
        $this->config = $config;
    }

    public function start(Workflow $workflow, array $context = []): WorkflowInstance
    {
        $instance = new WorkflowInstance();
        $instance->workflow_id = $workflow->id;
        $instance->status = 'running';
        $instance->context = $context;
        $instance->save();
        return $instance;
    }

    public function advance(WorkflowInstance $instance): WorkflowInstance
    {
        // Placeholder: add real step resolution/transition logic
        $instance->status = 'completed';
        $instance->save();
        return $instance;
    }
}

