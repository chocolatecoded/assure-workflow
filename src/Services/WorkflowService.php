<?php

namespace Assure\Workflow\Services;

use Assure\Workflow\Models\Workflow;
use Assure\Workflow\Models\WorkflowFormSubmission;
use Assure\Workflow\Models\WorkflowFormSubmissionStep;
use Assure\Workflow\Models\WorkflowStepCondition;

class WorkflowService
{
    public function getNextStep(int $technicianId, int $workOrderId, string $module, Workflow $workflow)
    {
        // Get technician_id from WorkflowWorkOrder->workOrderTechnicians
//        $workOrderTechnician = $workOrder->workOrderTechnicians()->first();

        // Check if there is an existing submission record for the WO and technician_id
        $existingSubmission = WorkflowFormSubmission::where('work_order_id', $workOrderId)
            ->when($module === 'PRA_COMPLETION', function ($query) {
                return $query->whereNotIn('work_order_status', ['Permit Approved', 'Awaiting Permit Approval']);
            }, function ($query) {
                return $query->whereIn('work_order_status', ['Permit Approved', 'Awaiting Permit Approval']);
            })
            ->where('technician_id', $technicianId)
            ->orderBy('created_at', 'desc')
            ->first();

        $nextStep = null;


        $action = 'NEXT_STEP';
        // TODO - check next page to load, complete PRA?
        if ($existingSubmission) {
            // check first if the current step is not yet recorded then we still use the current step
            // scenarios maybe user is just refreshing the page

            $currentStep = $existingSubmission->step;
            $currentStepExists = WorkflowFormSubmissionStep::where('workflow_form_submission_id', $existingSubmission->id)
                ->where('work_order_id', $workOrderId)
                ->where('work_flow_step_id', $currentStep->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // If step do not exist yet, we use it as current step
            if (!$currentStepExists && $module != 'PRA_CLOSURE') {
                $nextStep = $currentStep;
            } else {
                // Get the current step by going to the validation
                [$action, $nextStep] = $this->validateNextWorkflow($workflow, $existingSubmission, $module);
            }
        }

        // If no existing submission or no next step found, use the first step
        if (!$nextStep && $action != 'LAST_STEP') {
            $nextStep = $workflow->steps()
                ->orderBy('order')
                ->first();
        }

        $formUrl = '';
        if ($action === 'NEXT_STEP') {
            $formUrl = $this->getComposerProxyPath(
                config('app.composerUri'),
                $workOrderId,
                $nextStep->data['formId']
            );
        }

        return [
            'action' => $action,
            'workflowNextStepFormUrl' => $formUrl,
            'workflowFormSubmissions' => $existingSubmission,
        ];
    }

    private function getComposerProxyPath($path, $id, $formId, $assetId = null)
    {
        return url("/api/v3/composer", [$formId, $id, $assetId]);
    }

    private function validateNextWorkflow($workflow, $submission, $module)
    {
        //***************************************
        // process NEXT STEP
        // Get the steps of the attached workflow
        $currentStep = $submission->step;
        $workflowSteps = $workflow->steps()->where('module', $module)->orderBy('order', 'ASC')->get();
        $nextStep = null;
        $action = 'NEXT_STEP';

        foreach ($workflowSteps as $step) {
            // We need to check first if the step has attached conditions since those are the priority action
            $stepConditions = $step->conditions()
                ->whereHas('showStep', function ($query) use ($module) {
                    return $query->where('module', $module);
                })
                ->orderBy('order', 'ASC')
                ->get();
            foreach ($stepConditions as $condition) {
                // Get the answers
                $outputs = $submission->submissionsOutput()->where('name', $condition->name)->get();
                if ($step->match($outputs, $condition)) {
                    // check if condition is already recorded, if recorded - skip the condition and proceed to next condition/step checking
                    $isStepDone = WorkflowFormSubmissionStep::where('workflow_form_submission_id', $submission->id)
                        ->where('work_flow_step_id', $condition->showStep->id)->exists();

                    if ($condition->virtual_step || !$isStepDone) {
                        // stop checking, return $condition as next step
                        $nextStep = $condition;
                        break 2;
                    }
                }
            }

            // If step is greater than the current step and is not the current step, use it as nextStep
            if (($step->order > $currentStep->order) && ($step->order != $currentStep->order)) {
                // Add more checking, if step is a PERMIT we need to check if it was selected during PRA,
                // if not we will skip it
                if (in_array($step->type, ['PERMIT_OPEN', 'PERMIT_CLOSE', 'FORM_OPEN', 'FORM_CLOSE'])) {
                    // Get the linked conditions for the Permit
                    $stepConditions = $step->stepConditions()->orderBy('order', 'ASC')->get();
                    if (!$stepConditions->isEmpty()) {
                        // Set nextstep to null
                        $nextStep=null;

                        // the same logic in line 100, move this to a function during cleanup
                        foreach ($stepConditions as $condition) {
                            // Get the answers
                            $outputs = $submission->submissionsOutput()->where('name', $condition->name)->get();
                            if ($step->match($outputs, $condition)) {
                                // check if condition is already recorded, if recorded - skip the condition and proceed to next condition/step checking
                                $isStepDone = WorkflowFormSubmissionStep::where('workflow_form_submission_id', $submission->id)
                                    ->where('work_flow_step_id', $condition->showStep->id)->exists();

                                if (!$isStepDone) {
                                    // stop checking, return $condition as next step
                                    $nextStep = $condition;
                                    break;
                                }
                            }
                        }
                    } else {
                        /**
                         *  If step has no linked conditions, we checked if it's a FORM_OPEN or FORM_CLOSE
                         *  then we use that step If PERMIT_OPEN or PERMIT_CLOSE we skip it
                         */
                        if ($step->type === 'FORM_OPEN' || $step->type === 'FORM_CLOSE') {
                            $nextStep = $step;
                            break;
                        }

                        continue; // skip the Permit and proceed to next item
                    }
                } else {
                    // set next step
                    $nextStep = $step;
                }
            }

            if ($nextStep && $nextStep->type != 'NO APPROVAL') {
                break;
            }
        }

        // set final selected next step, add condition if WorkflowStepCondition then get the step using showStep
        $step = $nextStep;

        if ($nextStep instanceof WorkflowStepCondition) {
            if ($nextStep->virtual_step) {
                $action = $nextStep->virtual_step;
            }

            // If next step is WorkflowStepCondition, this means that it is from a condition, so we need to get
            // the parent or original step
            $step = $nextStep->showStep;
        }

        if ($step) {
            $submission->update([
                'work_flow_step_id' => $step->id,
            ]);
        } else {
            $action = 'LAST_STEP';
        }

        return [
            $action,
            $step,
        ];
    }
}