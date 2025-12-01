<?php

namespace Assure\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowInstance extends Model
{
    use SoftDeletes;

    protected $table = 'workflow_instances';
    protected $fillable = ['workflow_id', 'status', 'context'];
    protected $casts = [
        'context' => 'array',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}

