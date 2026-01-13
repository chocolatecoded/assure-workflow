<?php

namespace Assure\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowStep extends Model
{
    use SoftDeletes;
    protected $table = 'workflow_steps';
    protected $fillable = ['workflow_id', 'name', 'order', 'config', 'module', 'type', 'data', 'condition_citeria'];
    protected $casts = [
        'config' => 'array',
        'data' => 'array',
    ];


    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function conditions()
    {
        return $this->hasMany(WorkflowStepCondition::class, 'workflow_step_id');
    }

    public function submissionOutputs()
    {
        return $this->hasManyThrough(
            WorkflowFormSubmissionOutput::class,
            WorkflowFormSubmissionStep::class,
            'work_flow_step_id',
            'workflow_form_submissions_steps_id',
            'id',
            'id'
        );
    }

    public function stepConditions() {
        return $this->hasMany(WorkflowStepCondition::class, 'workflow_show_step_id');
    }

    public function metConditions($workflowFormSubmission)
    {
        // check if there are any conditions
        $conditions = $workflowFormSubmission->step->conditions()->get();

        // return true if step has no conditions
//        if ($conditions->isEmpty()) {
//            return true;
//        }

//
//        if ($this->condition_citeria == 'ALL') {
//            foreach ($conditions as $c) {
//                if (!$this->match($work, $c)) {
//                    return false;
//                }
//            }
//
//            return true;
//        } else {
//            foreach ($conditions as $c) {
//                if ($this->match($work, $c)) {
//                    return $c;
//                    break;
//                }
//            }
//
//            return false;
//        }

        foreach ($conditions as $c) {
//            $outputs = $workflowFormSubmission->submissionsOutput()->where('name', $c->condition_id)->get();
            $outputs = $this->submissionOutputs()->where('name', $c->condition_id)->get();
            if ($this->match($outputs, $c)) {
                return $c;
            }
//            return $this->match($outputs, $c);
        }

        return false;
    }

    public function match($workOutputs, WorkflowStepCondition $condition): bool
    {

        // get last submission id
//        $step = $work->steps()->where('workflow_step_id', $condition->workflow_step_id)
//            ->addSelect('work_steps.*')
//            ->addSelect('order')
//            ->leftJoin('workflow_steps', 'workflow_steps.id', 'work_steps.workflow_step_id')
//            ->orderBy('work_steps.id', 'desc')
//            ->first();
//
//
//        $lastStep = $work->steps()
//            ->addSelect('order')
//            ->leftJoin('workflow_steps', 'workflow_steps.id', 'work_steps.workflow_step_id')
//            ->orderBy('work_steps.id', 'desc')
//            ->first();
//
//
//        // check to make sure its been submitted last workflow session
//        if ($lastStep && $step && $lastStep->order < $step->order) {
//            return false;
//        }

//        if ($step) {
//            // get the output
//            $workOutputs = $step->data()
//                ->where('work_step_outputs.name', $condition->name)
//                ->get();
//        } else {
//            return false;
//        }

//        dd($workflowFormSubmission);
//        $workOutputs = $workflowFormSubmission->submissionsOutput()->get();
//        dd($workOutputs->toArray());
        $bool = false;
        foreach ($workOutputs as $key => $output) {
            switch ($condition->match_type) {
                case WorkflowStepCondition::CONTAINS:
                    if ($output && strpos(strtolower($output->value), strtolower($condition->value)) !== false) {
                        $bool = true;
                    }
                    break;

                case WorkflowStepCondition::NOTCONTAINS:
                    if (!$output || str_contains(strtolower($output->value), strtolower($condition->value)) !== true) {
                        $bool = true;
                    }
                    break;

                case WorkflowStepCondition::EQUALS:
                    // For now we assume that separator used is a "|", add additional support for other separator
                    $values = explode("|", $output->value);
                    $bool = collect($values)->contains(function ($value) use ($condition) {
//                        $output = explode("|", $condition->value);
                        return trim(strtolower($value)) === trim(strtolower($condition->value));
//                        return in_array($value, $output);
                    });

                    break;
                case WorkflowStepCondition::NOTEQUALS:
                    if ($output && strtolower($output->value) != strtolower($condition->value)) {
                        $bool = true;
                    }
                    break;

                case WorkflowStepCondition::EMPTY:
                    if (!$output) {
                        $bool = true;
                    }
                    break;

                case WorkflowStepCondition::NOTEMPTY:
                    if ($output) {
                        $bool = true;
                    }
                    break;
            }
        }

        return $bool;
    }
}

