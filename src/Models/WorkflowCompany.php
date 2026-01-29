<?php

namespace Assure\Workflow\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowCompany extends Model
{
    protected $table = 'company';

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'pra_form_configuration', 'id');
    }
}