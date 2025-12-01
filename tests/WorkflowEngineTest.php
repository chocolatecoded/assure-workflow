<?php

use Assure\Workflow\Services\ConfigurationManager;
use Assure\Workflow\Services\WorkflowEngine;
use Assure\Workflow\Models\Workflow;
use Assure\Workflow\Models\WorkflowInstance;

class WorkflowEngineTest extends \PHPUnit\Framework\TestCase
{
    public function testStartCreatesInstance()
    {
        $config = new ConfigurationManager([]);
        $engine = new WorkflowEngine($config);

        $workflow = new Workflow();
        $workflow->id = 1;

        // Stub Eloquent save
        WorkflowInstance::unguard();
        $instance = $engine->start($workflow, ['foo' => 'bar']);

        $this->assertInstanceOf(WorkflowInstance::class, $instance);
        $this->assertEquals('running', $instance->status);
        $this->assertEquals(['foo' => 'bar'], $instance->context);
    }
}

