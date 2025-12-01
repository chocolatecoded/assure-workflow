<?php

namespace Assure\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderWorkflowStepsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'order' => 'required|array',
            'order.*' => 'integer',
        ];
    }
}

