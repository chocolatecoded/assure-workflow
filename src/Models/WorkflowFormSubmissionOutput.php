<?php

namespace Assure\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowFormSubmissionOutput extends Model
{
    protected $table = 'workflow_form_submission_outputs';

    protected $fillable = [
        'workflow_form_submissions_steps_id',
        'name',
        'value',
        'condition_id',
    ];

    public function submissionStep()
    {
        return $this->belongsTo(WorkflowFormSubmissionStep::class, 'workflow_form_submissions_steps_id', 'id');
    }

    public function condition()
    {
        return $this->belongsTo(WorkflowStepCondition::class, 'condition_id', 'id');
    }
}

