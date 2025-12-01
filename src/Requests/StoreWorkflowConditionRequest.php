<?php

namespace Assure\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowConditionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'condition_type' => 'nullable|string',
            'condition_id' => 'nullable|string',
            'match_type' => 'nullable|string',
            'name' => 'nullable|string',
            'value' => 'nullable|string',
            'data' => 'nullable|array',
            'text' => 'nullable|string',
            'workflow_show_step_id' => 'nullable|integer',
        ];
    }
}

