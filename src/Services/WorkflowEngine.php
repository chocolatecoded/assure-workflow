<?php

namespace Assure\Workflow\Services;

use App\Models\WorkOrders;
use Assure\Workflow\Events\WorkflowPraComplete;
use Assure\Workflow\Models\Workflow;
use Assure\Workflow\Models\WorkflowStep;
use Assure\Workflow\Models\WorkflowInstance;
use Assure\Workflow\Models\WorkflowStepCondition;
use Assure\Workflow\Models\WorkflowFormSubmission;
use Assure\Workflow\Models\WorkflowFormSubmissionStep;
use Assure\Workflow\Models\WorkflowFormSubmissionOutput;
use Assure\Workflow\Models\WorkflowWorkOrder;

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

    public static function saveAnswer(array $answers, $content, $workflow, $module)
    {
        // Get the work order to access technicians
        $workOrder = WorkflowWorkOrder::where('work_order_no', $answers['workOrderNo'])->first();
        if (!$workOrder) {
            throw new \Exception("Work order not found: {$answers['workOrderNo']}");
        }

        // Get technician_id from WorkflowWorkOrder->workOrderTechnicians
        $workOrderTechnician = $workOrder->workOrderTechnicians()->first();
        if (!$workOrderTechnician) {
            throw new \Exception("No technician found for work order: {$answers['workOrderNo']}");
        }
        $technician_id = $workOrderTechnician->user_id;

        // Check if there is an existing submission record for the WO and technician_id
        $submission = WorkflowFormSubmission::where('work_order_id', $workOrder->id)
            ->when($module === 'PRA_COMPLETION', function ($query) use ($workOrder) {
                return $query->whereNotIn('work_order_status', ['Permit Approved', 'Awaiting Permit Approval']);
            }, function ($query) {
                return $query->whereIn('work_order_status', ['Permit Approved', 'Awaiting Permit Approval']);
            })
            ->where('technician_id', $technician_id)
            ->first();

        // Create new submission if no record exists
        if (!$submission) {
            // Fallback: try to get workflow from company if relationship exists
            if (!$workflow && $workOrder->company) {
                // This would need to be implemented based on your company-workflow relationship
                // For now, using the hardcoded approach similar to WorkflowController
            }

            if (!$workflow) {
                throw new \Exception("No workflow found for work order: {$workOrder->id}");
            }

            // Get the first step of the workflow
            $firstStep = $workflow->steps()->orderBy('order', 'ASC')->first();
            if (!$firstStep) {
                throw new \Exception("No steps found in workflow: {$workflow->id}");
            }

            // Create new submission
            $submission = WorkflowFormSubmission::create([
                'work_order_id' => $workOrder->id,
                'technician_id' => $technician_id,
                'work_flow_id' => $workflow->id,
                'work_flow_step_id' => $firstStep->id,
                'work_order_status' => WorkOrders::PRA_SUBMIT, // We set to default Pra Pending status
            ]);
        }

        // Get the current step from the workflow_form_submissions
        $currentStep = $submission->step;
        if (!$currentStep) {
            throw new \Exception("Current step not found for submission: {$submission->id}");
        }

        // Get the workflow
        $workflow = $currentStep->workflow;
        if (!$workflow) {
            throw new \Exception("Workflow not found for step: {$currentStep->id}");
        }


        //***************************************
        // Record only the answers that are in the conditions
        // If the step has conditions, only save answers matching those condition IDs/names
        // If the step has no conditions, save all answers (no filter to apply)

        // Record the current step in workflow_form_submissions_steps table
        $submissionStep = WorkflowFormSubmissionStep::create([
            'workflow_form_submission_id' => $submission->id,
            'work_order_id' => $workOrder->id,
            'work_flow_step_id' => $currentStep->id,
            'submission_id' => $content['submissionId'],
            'form_id' => $content['formID'],
        ]);

        // Get conditions for the current step to determine which answers to save
        $currentStepConditions = $currentStep->conditions()->get();

        $conditionIds = $currentStepConditions->map(function ($condition) {
            return $condition->name;
        })->filter()->unique()->toArray();

        foreach ($answers as $name => $value) {
            // Only save answers that match condition IDs/names (if conditions exist)
            if ($value == "" || !in_array($name, $conditionIds)) {
                continue;
            }

            // Handle array values similar to the original logic
            if (gettype($value) == 'array') {
                $elems = [];
                $boolType = false;

                foreach ($value as $n => $v) {
                    if ($v === '1') {
                        $elems[] = $n;
                    }

                    // This is for SELECT BOXES component
                    if ($v === true) {
                        $boolType = true;
                        WorkflowFormSubmissionOutput::create([
                            'workflow_form_submissions_steps_id' => $submissionStep->id,
                            'name' => $name,
                            'value' => $n,
                        ]);
                    }
                }

                if ($boolType == false) {
                    WorkflowFormSubmissionOutput::create([
                        'workflow_form_submissions_steps_id' => $submissionStep->id,
                        'name' => $name,
                        'value' => implode(", ", $elems),
                    ]);
                }
            } else {
                // check if answer belongs to a condition for easier access later on
                $condition = $currentStepConditions->filter(function ($condition) use ($value) {
                    return $condition->value == $value;
                })->first();
                WorkflowFormSubmissionOutput::create([
                    'workflow_form_submissions_steps_id' => $submissionStep->id,
                    'name' => $name,
                    'value' => $value,
                    'condition_id' => $condition ? $condition->id : null,
                ]);
            }
        }

        return [$submission, $currentStep];
    }

}

