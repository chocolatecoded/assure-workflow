<?php

namespace Assure\Workflow\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowFormSubmission extends Model
{
    protected $table = 'workflow_form_submissions';

    protected $fillable = [
        'work_order_id',
        'technician_id',
        'work_flow_id',
        'work_flow_step_id',
        'work_order_status',
    ];

    public function workflow()
    {
        return $this->hasMany(Workflow::class);
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'work_flow_step_id', 'id');
    }

    public function submissionsOutput()
    {
//        return $this->hasMany(WorkflowFormSubmissionOutput::class, 'workflow_form_submissions_steps_id', 'id');
        return $this->hasManyThrough(
            WorkflowFormSubmissionOutput::class,
            WorkflowFormSubmissionStep::class,
            'workflow_form_submission_id',
            'workflow_form_submissions_steps_id',
            'id',
            'id'
        );
    }

    public function submissionSteps()
    {
        return $this->hasMany(WorkflowFormSubmissionStep::class, 'work_order_id', 'work_order_id');
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkflowWorkOrder::class, 'work_order_id', 'id');
    }
}

