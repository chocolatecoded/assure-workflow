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
}

