<?php

namespace Assure\Workflow\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowFormSubmissionStep extends Model
{
    protected $table = 'workflow_form_submissions_steps';

    protected $fillable = [
        'workflow_form_submission_id',
        'work_order_id',
        'submission_id',
        'form_id',
        'work_flow_id',
        'work_flow_step_id',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'work_flow_id', 'id');
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'work_flow_step_id', 'id');
    }

    public function outputs()
    {
        return $this->hasMany(WorkflowFormSubmissionOutput::class, 'workflow_form_submissions_steps_id', 'id');
    }
}

