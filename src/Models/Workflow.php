<?php

namespace Assure\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use SoftDeletes;
    protected $table = 'workflows';
    protected $fillable = ['name', 'description', 'config'];
    protected $casts = [
        'config' => 'array',
    ];

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class);
    }
}

