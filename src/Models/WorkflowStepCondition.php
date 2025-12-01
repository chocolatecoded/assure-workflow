<?php

namespace Assure\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowStepCondition extends Model
{
    use SoftDeletes;

    protected $table = 'workflow_step_conditions';

    protected $fillable = [
        'workflow_step_id',
        'condition_type',
        'condition_id',
        'match_type',
        'name',
        'value',
        'data',
        'text',
        'workflow_show_step_id',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    public function showStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_show_step_id');
    }
}

